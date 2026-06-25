<?php

namespace App\Services;

use App\Models\User;
use App\Models\GoogleSheet;
use App\Models\GoogleSetting;
use App\Models\Transaction;
use Carbon\Carbon;

class GoogleSheetService
{
    private function getClient()
    {
        $setting = GoogleSetting::findOrFail(1);

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
     * Cari spreadsheet user yang sudah ada.
     * Jika belum ada, buat baru dengan template lengkap.
     */
    public function appendTransaction(Transaction $transaction)
    {
        $googleSheet = GoogleSheet::where('user_id', $transaction->user_id)->first();

        if (!$googleSheet) {

            $data = $this->createSpreadsheet($transaction->user_id);

            $googleSheet = GoogleSheet::create([
                'user_id'          => $transaction->user_id,
                'spreadsheet_id'   => $data['spreadsheet_id'],
                'spreadsheet_name' => $data['spreadsheet_name'],
                'spreadsheet_url'  => $data['spreadsheet_url'],
                'sheet_name'       => 'Transaksi',
            ]);
        }

        $this->appendRow($googleSheet, $transaction);

        /**
         * Setelah transaksi baru masuk, refresh ringkasan di Dashboard
         */
        $this->refreshDashboard($googleSheet, $transaction->user_id);
    }

    /**
     * Buat spreadsheet baru dengan template Dashboard + Transaksi
     */
    private function createSpreadsheet(int $userId)
    {
        $user = User::findOrFail($userId);

        $client  = $this->getClient();
        $service = new \Google\Service\Sheets($client);
        $drive   = new \Google\Service\Drive($client);

        $spreadsheetName = 'FinanceBot_' . $user->id . '_' . $user->name;

        $spreadsheet = new \Google\Service\Sheets\Spreadsheet([
            'properties' => [
                'title'    => $spreadsheetName,
                'locale'   => 'id_ID',
                'timezone' => 'Asia/Makassar',
            ],
            'sheets' => [
                ['properties' => [
                    'title' => 'Dashboard',
                    'gridProperties' => [
                        'frozenRowCount' => 1,
                        'columnCount'    => 12,
                    ],
                    'tabColor' => ['red' => 0.20, 'green' => 0.47, 'blue' => 0.95],
                ]],
                ['properties' => [
                    'title' => 'Transaksi',
                    'gridProperties' => [
                        'frozenRowCount' => 1,
                        'columnCount'    => 8,
                    ],
                    'tabColor' => ['red' => 0.16, 'green' => 0.66, 'blue' => 0.36],
                ]],
            ],
        ]);

        $spreadsheet = $service->spreadsheets->create($spreadsheet);
        $spreadsheetId = $spreadsheet->getSpreadsheetId();

        /**
         * Setup kedua sheet (header + styling)
         */
        $this->setupDashboard($service, $spreadsheetId);
        $this->setupTransactions($service, $spreadsheetId);

        /**
         * Pindahkan ke folder Drive khusus (jika ada)
         */
        if (env('GOOGLE_DRIVE_FOLDER_ID')) {
            try {
                $file = $drive->files->get($spreadsheetId, [
                    'fields' => 'parents',
                ]);
                $previousParents = implode(',', $file->parents);

                $drive->files->update(
                    $spreadsheetId,
                    new \Google\Service\Drive\DriveFile(),
                    [
                        'addParents'    => env('GOOGLE_DRIVE_FOLDER_ID'),
                        'removeParents' => $previousParents,
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Gagal pindahkan spreadsheet ke folder: ' . $e->getMessage());
            }
        }

        return [
            'spreadsheet_id'   => $spreadsheetId,
            'spreadsheet_name' => $spreadsheetName,
            'spreadsheet_url'  => $spreadsheet->getSpreadsheetUrl(),
        ];
    }

    /**
     * Setup sheet Dashboard: judul, ringkasan, placeholder grafik.
     */
    private function setupDashboard($service, string $spreadsheetId)
    {
        /**
         * Baris 1: judul utama (merge A1:L1)
         */
        $title = [[
            '💰 FINANCEBOT DASHBOARD',
        ]];

        $service->spreadsheets_values->update(
            $spreadsheetId,
            "'Dashboard'!A1",
            new \Google\Service\Sheets\ValueRange(['values' => $title]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Baris 3-6: kartu ringkasan (4 kolom: Saldo, Pemasukan, Pengeluaran, Total Transaksi)
         */
        $summaryLabels = [
            ['💳 Saldo Saat Ini',  '📅 Bulan Ini',  '', ''],
            ['💰 Total Pemasukan', '💸 Total Pengeluaran', '📊 Jumlah Transaksi', '📈 Rata-rata'],
        ];

        $service->spreadsheets_values->update(
            $spreadsheetId,
            "'Dashboard'!A3",
            new \Google\Service\Sheets\ValueRange(['values' => $summaryLabels]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Baris 4: nilai ringkasan kosong dulu (formula akan diisi saat ada data)
         */
        $summaryFormulas = [
            ['=IF(B8="","Rp 0",TEXT(B8,"Rp #,##0"))',
             '=TEXT(EOMONTH(TODAY(),0),"MMMM YYYY")', '', ''],
            ['=IF(B9="","Rp 0",TEXT(B9,"Rp #,##0"))',
             '=IF(B10="","Rp 0",TEXT(B10,"Rp #,##0"))',
             '=COUNTA(\'Transaksi\'!A2:A)',
             '=IF(B11="","Rp 0",TEXT(B11,"Rp #,##0"))'],
        ];

        $service->spreadsheets_values->update(
            $spreadsheetId,
            "'Dashboard'!A4",
            new \Google\Service\Sheets\ValueRange(['values' => $summaryFormulas]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Tabel pivot income vs expense per bulan (placeholder)
         */
        $monthlyHeaders = [
            ['Bulan', 'Pemasukan', 'Pengeluaran', 'Saldo'],
        ];

        $service->spreadsheets_values->update(
            $spreadsheetId,
            "'Dashboard'!A7",
            new \Google\Service\Sheets\ValueRange(['values' => $monthlyHeaders]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Styling Dashboard (merge cells, font, warna background)
         */
        $this->applyDashboardStyling($service, $spreadsheetId);
    }

    /**
     * Setup sheet Transaksi: header + styling.
     */
    private function setupTransactions($service, string $spreadsheetId)
    {
        $headers = [[
            'Tanggal',
            'Waktu',
            'Tipe',
            'Kategori',
            'Nominal',
            'Keterangan',
            'Bulan',
            'Tahun',
        ]];

        $service->spreadsheets_values->update(
            $spreadsheetId,
            "'Transaksi'!A1",
            new \Google\Service\Sheets\ValueRange(['values' => $headers]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        $this->applyTransactionsStyling($service, $spreadsheetId);
    }

    /**
     * Append baris transaksi baru ke sheet Transaksi
     */
    private function appendRow(GoogleSheet $googleSheet, Transaction $transaction)
    {
        $client  = $this->getClient();
        $service = new \Google\Service\Sheets($client);

        $values = [[
            $transaction->transaction_date->format('d/m/Y'),
            $transaction->transaction_date->format('H:i'),
            strtoupper($transaction->type),
            $transaction->category,
            $transaction->amount,
            $transaction->description,
            $transaction->transaction_date->format('Y-m'),
            $transaction->transaction_date->format('Y'),
        ]];

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values,
        ]);

        $service->spreadsheets_values->append(
            $googleSheet->spreadsheet_id,
            "'Transaksi'!A:H",
            $body,
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );
    }

    /**
     * Refresh isi Dashboard setelah ada transaksi baru.
     * Menghindari duplikasi spreadsheet - cukup update data saja.
     */
    public function refreshDashboard(GoogleSheet $googleSheet, int $userId)
    {
        $client  = $this->getClient();
        $service = new \Google\Service\Sheets($client);

        /**
         * Hitung ringkasan dari database
         */
        $income  = (float) Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $expense = (float) Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;
        $count   = Transaction::where('user_id', $userId)->count();
        $avg     = $count > 0 ? ($income + $expense) / $count : 0;

        /**
         * Tulis ringkasan di Dashboard
         */
        $summaryData = [[
            'Rp ' . number_format($balance, 0, ',', '.'),
            now()->format('F Y'),
            '',
            '',
        ], [
            'Rp ' . number_format($income, 0, ',', '.'),
            'Rp ' . number_format($expense, 0, ',', '.'),
            (string) $count,
            'Rp ' . number_format($avg, 0, ',', '.'),
        ]];

        $service->spreadsheets_values->update(
            $googleSheet->spreadsheet_id,
            "'Dashboard'!A4:D5",
            new \Google\Service\Sheets\ValueRange(['values' => $summaryData]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Generate baris per bulan (12 bulan terakhir)
         */
        $monthlyRows = [];
        for ($i = 11; $i >= 0; $i--) {
            $date   = Carbon::now()->subMonths($i);
            $start  = $date->copy()->startOfMonth();
            $end    = $date->copy()->endOfMonth();

            $monthIncome  = (float) Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthExpense = (float) Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $monthlyRows[] = [
                $date->format('F Y'),
                'Rp ' . number_format($monthIncome, 0, ',', '.'),
                'Rp ' . number_format($monthExpense, 0, ',', '.'),
                'Rp ' . number_format($monthIncome - $monthExpense, 0, ',', '.'),
            ];
        }

        $service->spreadsheets_values->update(
            $googleSheet->spreadsheet_id,
            "'Dashboard'!A8:D19",
            new \Google\Service\Sheets\ValueRange(['values' => $monthlyRows]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        /**
         * Tambahkan chart income vs expense (line + pie) kalau belum ada
         */
        $this->ensureCharts($service, $googleSheet->spreadsheet_id);
    }

    /**
     * Buat chart di sheet Dashboard (hanya jika belum ada).
     */
    private function ensureCharts($service, string $spreadsheetId)
    {
        try {
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);

            $dashboardSheet = null;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === 'Dashboard') {
                    $dashboardSheet = $sheet;
                    break;
                }
            }

            if (!$dashboardSheet) {
                return;
            }

            /**
             * Cek apakah chart sudah ada di sheet ini
             */
            $existingCharts = $spreadsheet->getSheets();
            $hasChart = false;
            foreach ($existingCharts as $sheet) {
                if (count($sheet->getCharts() ?? []) > 0) {
                    $hasChart = true;
                    break;
                }
            }

            if ($hasChart) {
                return;
            }

            $sheetId = $dashboardSheet->getProperties()->getSheetId();

            $requests = [
                /**
                 * Chart 1: Line chart - Pemasukan vs Pengeluaran per bulan
                 */
                new \Google\Service\Sheets\Request([
                    'addChart' => [
                        'chart' => [
                            'spec' => [
                                'title' => 'Tren Pemasukan vs Pengeluaran',
                                'basicChart' => [
                                    'chartType' => 'LINE',
                                    'legendPosition' => 'BOTTOM_LEGEND',
                                    'axis' => [
                                        ['position' => 'BOTTOM_AXIS', 'title' => 'Bulan'],
                                        ['position' => 'LEFT_AXIS',   'title' => 'Nominal (Rp)'],
                                    ],
                                    'domains' => [[
                                        'domain' => [
                                            'sourceRange' => [
                                                'sources' => [
                                                    new \Google\Service\Sheets\GridRange([
                                                        'sheetId'       => $sheetId,
                                                        'startRowIndex' => 7,
                                                        'endRowIndex'   => 19,
                                                        'startColumnIndex' => 0,
                                                        'endColumnIndex'   => 1,
                                                    ]),
                                                ],
                                            ],
                                        ],
                                    ]],
                                    'series' => [
                                        [
                                            'series' => [
                                                'sourceRange' => [
                                                    'sources' => [
                                                        new \Google\Service\Sheets\GridRange([
                                                            'sheetId'       => $sheetId,
                                                            'startRowIndex' => 7,
                                                            'endRowIndex'   => 19,
                                                            'startColumnIndex' => 1,
                                                            'endColumnIndex'   => 2,
                                                        ]),
                                                    ],
                                                ],
                                            ],
                                            'targetAxis' => 'LEFT_AXIS',
                                        ],
                                        [
                                            'series' => [
                                                'sourceRange' => [
                                                    'sources' => [
                                                        new \Google\Service\Sheets\GridRange([
                                                            'sheetId'       => $sheetId,
                                                            'startRowIndex' => 7,
                                                            'endRowIndex'   => 19,
                                                            'startColumnIndex' => 2,
                                                            'endColumnIndex'   => 3,
                                                        ]),
                                                    ],
                                                ],
                                            ],
                                            'targetAxis' => 'LEFT_AXIS',
                                        ],
                                    ],
                                ],
                            ],
                            'position' => [
                                'overlayPosition' => [
                                    'anchorCell' => [
                                        'sheetId'         => $sheetId,
                                        'rowIndex'        => 21,
                                        'columnIndex'     => 0,
                                    ],
                                    'widthPixels'  => 600,
                                    'heightPixels' => 350,
                                ],
                            ],
                        ],
                    ],
                ]),

                /**
                 * Chart 2: Bar chart - Saldo per bulan
                 */
                new \Google\Service\Sheets\Request([
                    'addChart' => [
                        'chart' => [
                            'spec' => [
                                'title' => 'Saldo per Bulan',
                                'basicChart' => [
                                    'chartType' => 'COLUMN',
                                    'legendPosition' => 'BOTTOM_LEGEND',
                                    'axis' => [
                                        ['position' => 'BOTTOM_AXIS', 'title' => 'Bulan'],
                                        ['position' => 'LEFT_AXIS',   'title' => 'Saldo (Rp)'],
                                    ],
                                    'domains' => [[
                                        'domain' => [
                                            'sourceRange' => [
                                                'sources' => [
                                                    new \Google\Service\Sheets\GridRange([
                                                        'sheetId'       => $sheetId,
                                                        'startRowIndex' => 7,
                                                        'endRowIndex'   => 19,
                                                        'startColumnIndex' => 0,
                                                        'endColumnIndex'   => 1,
                                                    ]),
                                                ],
                                            ],
                                        ],
                                    ]],
                                    'series' => [
                                        [
                                            'series' => [
                                                'sourceRange' => [
                                                    'sources' => [
                                                        new \Google\Service\Sheets\GridRange([
                                                            'sheetId'       => $sheetId,
                                                            'startRowIndex' => 7,
                                                            'endRowIndex'   => 19,
                                                            'startColumnIndex' => 3,
                                                            'endColumnIndex'   => 4,
                                                        ]),
                                                    ],
                                                ],
                                            ],
                                            'targetAxis' => 'LEFT_AXIS',
                                        ],
                                    ],
                                ],
                            ],
                            'position' => [
                                'overlayPosition' => [
                                    'anchorCell' => [
                                        'sheetId'         => $sheetId,
                                        'rowIndex'        => 21,
                                        'columnIndex'     => 6,
                                    ],
                                    'widthPixels'  => 500,
                                    'heightPixels' => 350,
                                ],
                            ],
                        ],
                    ],
                ]),
            ];

            $service->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests,
                ])
            );

        } catch (\Exception $e) {
            \Log::warning('Gagal membuat chart: ' . $e->getMessage());
        }
    }

    /**
     * Styling untuk Dashboard
     */
    private function applyDashboardStyling($service, string $spreadsheetId)
    {
        try {
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheetId     = null;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === 'Dashboard') {
                    $sheetId = $sheet->getProperties()->getSheetId();
                    break;
                }
            }

            if ($sheetId === null) {
                return;
            }

            $requests = [
                /**
                 * Merge A1:L1 (judul)
                 */
                new \Google\Service\Sheets\Request([
                    'mergeCells' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 0,
                            'endRowIndex'      => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 12,
                        ],
                        'mergeType' => 'MERGE_ALL',
                    ],
                ]),

                /**
                 * Style judul utama
                 */
                new \Google\Service\Sheets\Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 0,
                            'endRowIndex'      => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 12,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'backgroundColor' => ['red' => 0.10, 'green' => 0.30, 'blue' => 0.85],
                                'horizontalAlignment' => 'CENTER',
                                'verticalAlignment'   => 'MIDDLE',
                                'textFormat' => [
                                    'foregroundColor' => ['red' => 1, 'green' => 1, 'blue' => 1],
                                    'fontSize'        => 20,
                                    'bold'            => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,verticalAlignment)',
                    ],
                ]),

                /**
                 * Style baris header ringkasan (baris 3)
                 */
                new \Google\Service\Sheets\Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 2,
                            'endRowIndex'      => 3,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 4,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'backgroundColor' => ['red' => 0.92, 'green' => 0.95, 'blue' => 1.0],
                                'horizontalAlignment' => 'CENTER',
                                'textFormat' => [
                                    'fontSize' => 11,
                                    'bold'     => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment)',
                    ],
                ]),

                /**
                 * Style nilai ringkasan (baris 4)
                 */
                new \Google\Service\Sheets\Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 3,
                            'endRowIndex'      => 5,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 4,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'horizontalAlignment' => 'CENTER',
                                'textFormat' => [
                                    'fontSize' => 14,
                                    'bold'     => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(textFormat,horizontalAlignment)',
                    ],
                ]),

                /**
                 * Style header tabel bulanan (baris 7)
                 */
                new \Google\Service\Sheets\Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 6,
                            'endRowIndex'      => 7,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 4,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'backgroundColor' => ['red' => 0.20, 'green' => 0.47, 'blue' => 0.95],
                                'horizontalAlignment' => 'CENTER',
                                'textFormat' => [
                                    'foregroundColor' => ['red' => 1, 'green' => 1, 'blue' => 1],
                                    'bold' => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment)',
                    ],
                ]),

                /**
                 * Set lebar kolom
                 */
                new \Google\Service\Sheets\Request([
                    'updateDimensionProperties' => [
                        'range' => [
                            'sheetId'   => $sheetId,
                            'dimension' => 'COLUMNS',
                            'startIndex' => 0,
                            'endIndex'   => 4,
                        ],
                        'properties' => [
                            'pixelSize' => 160,
                        ],
                        'fields' => 'pixelSize',
                    ],
                ]),
            ];

            $service->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests,
                ])
            );

        } catch (\Exception $e) {
            \Log::warning('Gagal styling dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Styling untuk sheet Transaksi
     */
    private function applyTransactionsStyling($service, string $spreadsheetId)
    {
        try {
            $spreadsheet = $service->spreadsheets->get($spreadsheetId);
            $sheetId     = null;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === 'Transaksi') {
                    $sheetId = $sheet->getProperties()->getSheetId();
                    break;
                }
            }

            if ($sheetId === null) {
                return;
            }

            $requests = [
                /**
                 * Style header (baris 1)
                 */
                new \Google\Service\Sheets\Request([
                    'repeatCell' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 0,
                            'endRowIndex'      => 1,
                            'startColumnIndex' => 0,
                            'endColumnIndex'   => 8,
                        ],
                        'cell' => [
                            'userEnteredFormat' => [
                                'backgroundColor' => ['red' => 0.16, 'green' => 0.66, 'blue' => 0.36],
                                'horizontalAlignment' => 'CENTER',
                                'textFormat' => [
                                    'foregroundColor' => ['red' => 1, 'green' => 1, 'blue' => 1],
                                    'bold' => true,
                                ],
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment)',
                    ],
                ]),

                /**
                 * Set lebar kolom
                 */
                new \Google\Service\Sheets\Request([
                    'updateDimensionProperties' => [
                        'range' => [
                            'sheetId'   => $sheetId,
                            'dimension' => 'COLUMNS',
                            'startIndex' => 0,
                            'endIndex'   => 8,
                        ],
                        'properties' => [
                            'pixelSize' => 130,
                        ],
                        'fields' => 'pixelSize',
                    ],
                ]),
            ];

            $service->spreadsheets->batchUpdate(
                $spreadsheetId,
                new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => $requests,
                ])
            );

        } catch (\Exception $e) {
            \Log::warning('Gagal styling transaksi: ' . $e->getMessage());
        }
    }
}
