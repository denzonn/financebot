<?php

namespace App\Services;

use App\Models\User;
use App\Models\GoogleSetting;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    /**
     * Klien Google API yang sudah di-refresh token-nya kalau perlu.
     */
    private function getClient(): \Google\Client
    {
        $setting = GoogleSetting::find(1);

        if (!$setting || !$setting->refresh_token) {
            throw new \RuntimeException(
                'Google belum terhubung. Silakan connect dari dashboard.'
            );
        }

        $client = new \Google\Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

        $client->setAccessToken([
            'access_token'  => $setting->access_token,
            'refresh_token' => $setting->refresh_token,
        ]);

        if ($client->isAccessTokenExpired()) {

            $token = $client->fetchAccessTokenWithRefreshToken($setting->refresh_token);

            $setting->update([
                'access_token' => $token['access_token'],
                'expires_at'   => now()->addSeconds($token['expires_in']),
            ]);

            $client->setAccessToken($token);
        }

        return $client;
    }

    /**
     * Entry point: append transaksi baru ke sheet user.
     * Kalau user belum punya sheet, copy template dulu.
     * Setelah append, refresh ringkasan Dashboard (AA:AB)
     * dan kartu summary (A42 / E42 / I42 / A47 / E47 / I47).
     */
    public function appendTransaction(Transaction $transaction)
    {
        $spreadsheetId = $this->getOrCreateUserSheet(
            $transaction->user_id
        );

        $this->appendRow($spreadsheetId, $transaction);

        $this->refreshDashboard(
            $spreadsheetId,
            $transaction->user_id
        );
    }

    /**
     * Ambil spreadsheet_id untuk user. Kalau belum ada,
     * copy template (GOOGLE_SHEET_TEMPLATE_ID) ke folder
     * (GOOGLE_DRIVE_FOLDER_ID), simpan mapping, dan return id.
     */
    public function getOrCreateUserSheet(int $userId): string
    {
        $setting = GoogleSetting::find(1);
        $map     = $setting?->spreadsheet_map ?? [];

        if (isset($map[$userId]) && $map[$userId]) {
            return $map[$userId];
        }

        $newSpreadsheetId = $this->copyTemplate($userId);

        $map[$userId] = $newSpreadsheetId;

        if ($setting) {
            $setting->update(['spreadsheet_map' => $map]);
        }

        return $newSpreadsheetId;
    }

    /**
     * Copy template spreadsheet ke akun Google, simpan di folder
     * yang dikonfigurasi. Return id spreadsheet hasil copy.
     */
    private function copyTemplate(int $userId): string
    {
        $templateId = env('GOOGLE_SHEET_TEMPLATE_ID');

        if (!$templateId) {
            throw new \RuntimeException(
                'GOOGLE_SHEET_TEMPLATE_ID belum di-set di .env'
            );
        }

        $user   = User::findOrFail($userId);
        $client = $this->getClient();
        $drive  = new \Google\Service\Drive($client);

        $copyName = 'FinanceBot_' . $user->id . '_' . $user->name;

        $copiedFile = $drive->files->copy(
            $templateId,
            new \Google\Service\Drive\DriveFile(['name' => $copyName])
        );

        $spreadsheetId = $copiedFile->getId();

        /**
         * Pindahkan ke folder khusus kalau di-set.
         */
        if (env('GOOGLE_DRIVE_FOLDER_ID')) {
            try {

                $file = $drive->files->get($spreadsheetId, [
                    'fields' => 'parents',
                ]);

                $previousParents = implode(',', $file->parents ?: []);

                $drive->files->update(
                    $spreadsheetId,
                    new \Google\Service\Drive\DriveFile(),
                    [
                        'addParents'    => env('GOOGLE_DRIVE_FOLDER_ID'),
                        'removeParents' => $previousParents,
                    ]
                );
            } catch (\Exception $e) {

                Log::warning(
                    'Gagal pindahkan sheet ke folder: ' .
                        $e->getMessage()
                );
            }
        }

        return $spreadsheetId;
    }

    /**
     * Append baris transaksi ke sheet "Transaksi" (A:E).
     * Template hanya punya 5 kolom:
     *   A = Tanggal, B = Tipe, C = Kategori,
     *   D = Nominal,  E = Keterangan.
     */
    private function appendRow(
        string $spreadsheetId,
        Transaction $transaction
    ) {
        $client  = $this->getClient();
        $service = new \Google\Service\Sheets($client);

        $values = [[
            $transaction->transaction_date->format('d/m/Y'),
            $transaction->type === 'income'
                ? 'Pemasukan'
                : 'Pengeluaran',
            $transaction->category ?? 'Lainnya',
            (float) $transaction->amount,
            $transaction->description ?? '',
        ]];

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values,
        ]);

        $service->spreadsheets_values->append(
            $spreadsheetId,
            "'Transaksi'!A:E",
            $body,
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );
    }

    /**
     * Hitung ulang ringkasan dari tabel transactions user,
     * lalu tulis ke kolom tersembunyi AA:AB sheet Dashboard
     * yang dipakai oleh 3 chart (pie, bar, line).
     * Juga tulis ulang kartu summary di A42 / E42 / I42 /
     * A47 / E47 / I47.
     */
    private function refreshDashboard(
        string $spreadsheetId,
        int $userId
    ): void {

        try {

            $client  = $this->getClient();
            $service = new \Google\Service\Sheets($client);

            /**
             * Hitung dari DB user, single source of truth.
             */
            $totalIncome  = (float) Transaction::where(
                'user_id', $userId
            )
                ->where('type', 'income')
                ->sum('amount');

            $totalExpense = (float) Transaction::where(
                'user_id', $userId
            )
                ->where('type', 'expense')
                ->sum('amount');

            $balance = $totalIncome - $totalExpense;

            /**
             * Top 5 kategori pengeluaran (untuk bar chart).
             * Kategori yang dipakai di template:
             *   Makanan, Belanja, Transport, Tagihan, Lainnya.
             */
            $topCategories = Transaction::where(
                'user_id', $userId
            )
                ->where('type', 'expense')
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $categoryRows = $topCategories->map(
                fn ($row) => [
                    $row->category ?? 'Lainnya',
                    (float) $row->total,
                ]
            )->values()->all();

            /**
             * 6 bulan terakhir: saldo kumulatif (income - expense)
             * per bulan, untuk line chart Perkembangan Saldo.
             */
            $monthlyRows = [];
            $runningBalance = 0;

            for ($i = 5; $i >= 0; $i--) {

                $month = Carbon::now()->subMonths($i);

                $incomeMonth  = (float) Transaction::where(
                    'user_id', $userId
                )
                    ->where('type', 'income')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount');

                $expenseMonth = (float) Transaction::where(
                    'user_id', $userId
                )
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount');

                $runningBalance += ($incomeMonth - $expenseMonth);

                $monthlyRows[] = [
                    $month->translatedFormat('M'),
                    $runningBalance,
                ];
            }

            /**
             * Susun nilai AA:AB sesuai urutan di template.
             *   AA1:AA2   = Income, Expense          (pie)
             *   AA5:AA9   = 5 kategori terbesar      (bar)
             *   AA12:AA17 = 6 bulan terakhir         (line)
             * Sel kosong di-fill string kosong supaya
             * chart tidak membaca baris kosong sebagai data.
             */
            $rows = array_fill(0, 17, ['', '']);

            $rows[0] = ['Income',  $totalIncome];
            $rows[1] = ['Expense', $totalExpense];

            foreach ($categoryRows as $i => $cat) {
                $rows[4 + $i] = $cat;
            }

            foreach ($monthlyRows as $i => $m) {
                $rows[11 + $i] = $m;
            }

            $dashboardBody = new \Google\Service\Sheets\ValueRange([
                'values' => $rows,
            ]);

            $service->spreadsheets_values->update(
                $spreadsheetId,
                "'Dashboard'!AA1:AB17",
                $dashboardBody,
                ['valueInputOption' => 'USER_ENTERED']
            );

            /**
             * Update kartu summary.
             * Template pakai merged cells, jadi tulis ke
             * sel kiri-atas tiap range merge.
             */
            $totalTransactions = Transaction::where(
                'user_id', $userId
            )->count();

            $daysWithData = max(
                1,
                (int) Transaction::where(
                    'user_id', $userId
                )
                    ->select(DB::raw(
                        'COUNT(DISTINCT DATE(transaction_date)) as d'
                    ))
                    ->value('d')
            );

            $avgDaily = $totalExpense / $daysWithData;

            $topCategory = $topCategories->first();
            $topCategoryName = $topCategory
                ? ($topCategory->category ?? 'Lainnya')
                : '-';

            $summaryBody = new \Google\Service\Sheets\ValueRange([
                'values' => [
                    [
                        "💰 TOTAL PEMASUKAN\nRp " . number_format(
                            $totalIncome,
                            0,
                            ',',
                            '.'
                        ),
                        '',
                        '',
                        '',
                        "💸 TOTAL PENGELUARAN\nRp " . number_format(
                            $totalExpense,
                            0,
                            ',',
                            '.'
                        ),
                        '',
                        '',
                        '',
                        "💳 SALDO\nRp " . number_format(
                            $balance,
                            0,
                            ',',
                            '.'
                        ),
                    ],
                    ['', '', '', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', '', '', ''],
                    ['', '', '', '', '', '', '', '', ''],
                    [
                        "🔥 TOTAL TRANSAKSI\n{$totalTransactions}",
                        '',
                        '',
                        '',
                        "📊 RATA-RATA HARIAN\nRp " . number_format(
                            $avgDaily,
                            0,
                            ',',
                            '.'
                        ),
                        '',
                        '',
                        '',
                        "🏆 KATEGORI TERBESAR\n{$topCategoryName}",
                    ],
                ],
            ]);

            $service->spreadsheets_values->update(
                $spreadsheetId,
                "'Dashboard'!A42:I47",
                $summaryBody,
                ['valueInputOption' => 'USER_ENTERED']
            );
        } catch (\Exception $e) {

            Log::warning(
                'Gagal refresh dashboard sheet: ' . $e->getMessage()
            );
        }
    }
}
