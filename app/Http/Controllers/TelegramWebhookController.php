<?php

namespace App\Http\Controllers;

use App\Models\TelegramAccount;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $message = data_get(
            $request->all(),
            'message'
        );

        if (!$message) {

            return response()->json([
                'success' => true
            ]);
        }

        $chatId = data_get(
            $message,
            'chat.id'
        );

        $telegramId = data_get(
            $message,
            'from.id'
        );

        $username = data_get(
            $message,
            'from.username'
        );

        $firstName = data_get(
            $message,
            'from.first_name'
        );

        $lastName = data_get(
            $message,
            'from.last_name'
        );

        $fullName = trim(
            $firstName . ' ' . $lastName
        );

        $text = trim(
            data_get(
                $message,
                'text',
                ''
            )
        );

        /**
         * START
         */
        if ($text === '/start') {

            $telegramAccount =
                TelegramAccount::where(
                    'telegram_id',
                    $telegramId
                )->first();

            if (!$telegramAccount) {

                $this->sendMessage(
                    $chatId,
                    "👋 Selamat datang di FinanceBot.\n\n" .
                        "Akun Telegram Anda belum terhubung.\n\n" .
                        "1. Login ke FinanceBot\n" .
                        "2. Buka menu Telegram Bot\n" .
                        "3. Copy command connect\n" .
                        "4. Kirim ke bot ini\n\n" .
                        "Contoh:\n" .
                        "/connect FB-XXXXXXX"
                );

                return response()->json([
                    'success' => true
                ]);
            }

            if ($this->isConnectionExpired($telegramAccount)) {

                $this->disconnectTelegram(
                    $telegramAccount
                );

                $this->sendMessage(
                    $chatId,
                    "⌛ Koneksi Telegram Anda telah kedaluwarsa.\n\n" .
                        "Silakan hubungkan ulang akun dari dashboard FinanceBot."
                );

                return response()->json([
                    'success' => true
                ]);
            }

            $this->sendMessage(
                $chatId,
                "🎉 FinanceBot Siap Digunakan\n\n" .

                    "💰 Contoh Pemasukan\n" .
                    "+500000 Jual Logo\n\n" .

                    "💸 Contoh Pengeluaran\n" .
                    "-25000 Beli Kopi\n\n" .

                    "📋 Menu Tersedia\n" .
                    "/saldo - Lihat saldo\n" .
                    "/laporan - Ringkasan bulan ini\n" .
                    "/format - Format transaksi\n" .
                    "/help - Bantuan"
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * CONNECT
         */
        if (
            str_starts_with(
                strtolower($text),
                '/connect'
            )
        ) {

            $parts = explode(
                ' ',
                $text
            );

            if (!isset($parts[1])) {

                $this->sendMessage(
                    $chatId,
                    "❌ Format salah.\n\n" .
                        "Contoh:\n" .
                        "/connect FB-XXXXXXX"
                );

                return response()->json([
                    'success' => true
                ]);
            }

            $code = strtoupper(
                trim($parts[1])
            );

            /**
             * Cari connect code
             */
            $telegramAccount =
                TelegramAccount::where(
                    'connect_code',
                    $code
                )->first();

            if (!$telegramAccount) {

                $this->sendMessage(
                    $chatId,
                    "❌ Kode tidak ditemukan."
                );

                return response()->json([
                    'success' => true
                ]);
            }

            /**
             * Telegram sudah dipakai akun lain?
             */
            $alreadyConnected =
                TelegramAccount::where(
                    'telegram_id',
                    $telegramId
                )->where(
                    'user_id',
                    '!=',
                    $telegramAccount->user_id
                )->first();

            if ($alreadyConnected) {

                $this->sendMessage(
                    $chatId,
                    "⚠️ Telegram ini sudah terhubung ke akun FinanceBot lain."
                );

                return response()->json([
                    'success' => true
                ]);
            }

            /**
             * Update record yang sudah ada (satu TelegramAccount per user).
             * Buat record baru hanya jika user ini belum punya row sama sekali.
             */
            TelegramAccount::updateOrCreate(
                ['user_id' => $telegramAccount->user_id],
                [
                    'telegram_id' => $telegramId,
                    'telegram_username' => $username,
                    'telegram_name' => $fullName,
                    'connect_code' => null,
                    'connected_at' => now(),
                ]
            );

            $this->sendMessage(
                $chatId,
                "🎉 Telegram berhasil terhubung.\n\n" .
                    "FinanceBot siap digunakan.\n\n" .

                    "💰 Contoh Pemasukan\n" .
                    "+500000 Jual Logo\n\n" .

                    "💸 Contoh Pengeluaran\n" .
                    "-25000 Beli Kopi\n\n" .

                    "👇 Jangan lupa bergabung ke komunitas FinanceBot untuk update fitur dan informasi terbaru.",
                [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '🚀 Gabung Komunitas',
                                'url' => 'https://t.me/+2uqDcsPvQN8yMzI1'
                            ]
                        ]
                    ]
                ]
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * CEK TELEGRAM TERHUBUNG
         */
        $telegramAccount =
            TelegramAccount::where(
                'telegram_id',
                $telegramId
            )->first();

        if (!$telegramAccount) {

            $this->sendMessage(
                $chatId,
                "🔒 Akun Telegram belum terhubung.\n\n" .
                    "Silakan buka dashboard FinanceBot lalu lakukan connect terlebih dahulu."
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * CEK EXPIRED
         */
        if ($this->isConnectionExpired($telegramAccount)) {

            $this->disconnectTelegram(
                $telegramAccount
            );

            $this->sendMessage(
                $chatId,
                "⌛ Koneksi Telegram telah kedaluwarsa.\n\n" .
                    "Silakan hubungkan ulang akun dari dashboard FinanceBot."
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * FORMAT
         */
        if ($text === '/format') {

            $this->sendMessage(
                $chatId,
                "📌 Format yang Didukung\n\n" .

                    "💰 Pemasukan\n" .
                    "+500000 Jual Logo\n" .
                    "+500.000 Jual Logo\n" .
                    "+Rp500000 Jual Logo\n\n" .

                    "💸 Pengeluaran\n" .
                    "-25000 Beli Kopi\n" .
                    "-25.000 Beli Kopi\n" .
                    "-Rp25000 Beli Kopi\n\n" .

                    "📝 Multi Transaksi\n" .
                    "+500000 Jual Logo\n" .
                    "-25000 Beli Kopi\n" .
                    "-5000 Parkir\n\n" .

                    "📊 Lainnya\n" .
                    "/saldo\n" .
                    "/format"
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * SALDO
         */
        if ($text === '/saldo') {

            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $telegramAccount->user_id
                ],
                [
                    'balance' => 0
                ]
            );

            $this->sendMessage(
                $chatId,
                "💳 SALDO SAAT INI\n\n" .
                    "Rp " .
                    number_format(
                        $wallet->balance,
                        0,
                        ',',
                        '.'
                    )
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * LAPORAN
         */
        if ($text === '/laporan') {

            $income = Transaction::where(
                'user_id',
                $telegramAccount->user_id
            )
                ->where('type', 'income')
                ->whereYear(
                    'transaction_date',
                    now()->year
                )
                ->whereMonth(
                    'transaction_date',
                    now()->month
                )
                ->sum('amount');

            $expense = Transaction::where(
                'user_id',
                $telegramAccount->user_id
            )
                ->where('type', 'expense')
                ->whereYear(
                    'transaction_date',
                    now()->year
                )
                ->whereMonth(
                    'transaction_date',
                    now()->month
                )
                ->sum('amount');

            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $telegramAccount->user_id
                ],
                [
                    'balance' => 0
                ]
            );

            $this->sendMessage(
                $chatId,
                "📊 LAPORAN SINGKAT\n\n" .

                    "💰 Pemasukan\nRp " .
                    number_format(
                        $income,
                        0,
                        ',',
                        '.'
                    ) .

                    "\n\n💸 Pengeluaran\nRp " .
                    number_format(
                        $expense,
                        0,
                        ',',
                        '.'
                    ) .

                    "\n\n💳 Saldo\nRp " .
                    number_format(
                        $wallet->balance,
                        0,
                        ',',
                        '.'
                    )
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**
         * HELP
         */
        if ($text === '/help') {

            $this->sendMessage(
                $chatId,
                "🤖 FINANCEBOT HELP\n\n" .

                    "💰 Catat Pemasukan\n" .
                    "+500000 Jual Logo\n\n" .

                    "💸 Catat Pengeluaran\n" .
                    "-25000 Beli Kopi\n\n" .

                    "📝 Multi Transaksi\n" .
                    "+500000 Jual Logo\n" .
                    "-25000 Beli Kopi\n" .
                    "-5000 Parkir\n\n" .

                    "📋 COMMAND\n" .
                    "/saldo\n" .
                    "/laporan\n" .
                    "/format\n" .
                    "/help"
            );

            return response()->json([
                'success' => true
            ]);
        }

        /**

         * TRANSAKSI (SUPPORT MULTI LINE)
         */
        $lines = preg_split(
            '/\r\n|\r|\n/',
            trim($text)
        );

        $parsedTransactions = [];

        foreach ($lines as $line) {

            $line = trim($line);

            if (!$line) {
                continue;
            }

            if (
                !preg_match(
                    '/^([+-])\s*(?:rp)?\s*([\d\.,]+)\s+(.+)$/i',
                    $line,
                    $matches
                )
            ) {

                $this->sendMessage(
                    $chatId,
                    "❌ Baris tidak dikenali: \"{$line}\"\n\n" .
                        "Gunakan format:\n" .
                        "+500000 Jual Logo\n" .
                        "-25000 Beli Kopi"
                );

                return response()->json([
                    'success' => true
                ]);
            }

            $description = trim(
                $matches[3] ?? ''
            );

            if (mb_strlen($description) < 3) {

                $this->sendMessage(
                    $chatId,
                    "❌ Keterangan transaksi wajib diisi.\n\n" .
                        "Contoh:\n" .
                        "+20000 Uang Jajan\n" .
                        "-5000 Beli Kopi"
                );

                return response()->json([
                    'success' => true
                ]);
            }

            $amount = (float) preg_replace(
                '/[^0-9]/',
                '',
                $matches[2]
            );

            if ($amount <= 0) {

                $this->sendMessage(
                    $chatId,
                    "❌ Nominal tidak valid: \"{$line}\"\n\n" .
                        "Nominal harus lebih dari 0."
                );

                return response()->json([
                    'success' => true
                ]);
            }

            $parsedTransactions[] = [
                'type' => $matches[1] == '+'
                    ? 'income'
                    : 'expense',

                'amount' => $amount,

                'description' => $description,
            ];
        }

        if (count($parsedTransactions) > 0) {

            DB::beginTransaction();

            try {

                $userId =
                    $telegramAccount->user_id;

                $wallet = Wallet::firstOrCreate(
                    [
                        'user_id' => $userId
                    ],
                    [
                        'balance' => 0
                    ]
                );

                $summary = [];

                foreach (
                    $parsedTransactions
                    as $trx
                ) {

                    $type =
                        $trx['type'];

                    $amount =
                        $trx['amount'];

                    $description =
                        $trx['description'];

                    $category =
                        $this->detectCategory(
                            $description,
                            $type
                        );

                    /**
                     * Cek saldo
                     */
                    if (
                        $type === 'expense'
                    ) {

                        if (
                            $wallet->balance <= 0
                        ) {

                            DB::rollBack();

                            $this->sendMessage(
                                $chatId,
                                "❌ Saldo Anda kosong.\n\n" .
                                    "Tidak dapat mencatat pengeluaran."
                            );

                            return response()->json([
                                'success' => true
                            ]);
                        }

                        if (
                            $wallet->balance <
                            $amount
                        ) {

                            DB::rollBack();

                            $this->sendMessage(
                                $chatId,
                                "❌ Saldo tidak mencukupi.\n\n" .
                                    "Saldo Saat Ini: Rp " .
                                    number_format(
                                        $wallet->balance,
                                        0,
                                        ',',
                                        '.'
                                    ) .
                                    "\nPengeluaran: Rp " .
                                    number_format(
                                        $amount,
                                        0,
                                        ',',
                                        '.'
                                    )
                            );

                            return response()->json([
                                'success' => true
                            ]);
                        }
                    }

                    $transaction = Transaction::create([
                        'user_id' => $userId,
                        'category' => $category,
                        'type' => $type,
                        'amount' => $amount,
                        'description' => $description,
                        'receipt_photo' => null,
                        'transaction_date' => now(),
                    ]);

                    if (
                        $type === 'income'
                    ) {

                        $wallet->increment(
                            'balance',
                            $amount
                        );

                        $summary[] =
                            "💰 Rp " .
                            number_format(
                                $amount,
                                0,
                                ',',
                                '.'
                            ) .
                            " - " .
                            $description;
                    } else {

                        $wallet->decrement(
                            'balance',
                            $amount
                        );

                        $summary[] =
                            "💸 Rp " .
                            number_format(
                                $amount,
                                0,
                                ',',
                                '.'
                            ) .
                            " - " .
                            $description;
                    }

                    $wallet->refresh();
                }

                $telegramAccount->update([
                    'connected_at' => now()
                ]);

                DB::commit();

                /**
                 * Sync ke Google Sheets di LUAR transaction.
                 * Jika gagal (mis. user belum connect Google),
                 * transaksi DB tetap aman dan user tetap mendapat notifikasi sukses.
                 */
                foreach (
                    $parsedTransactions
                    as $trx
                ) {

                    $sheetTransaction = Transaction::where(
                        'user_id',
                        $userId
                    )
                        ->where(
                            'amount',
                            $trx['amount']
                        )
                        ->where(
                            'description',
                            $trx['description']
                        )
                        ->where(
                            'type',
                            $trx['type']
                        )
                        ->latest('id')
                        ->first();

                    if ($sheetTransaction) {

                        try {

                            app(
                                \App\Services\GoogleSheetService::class
                            )->appendTransaction(
                                $sheetTransaction
                            );
                        } catch (\Exception $e) {

                            Log::warning(
                                'Google Sheet sync gagal: ' .
                                    $e->getMessage()
                            );
                        }
                    }
                }

                $this->sendMessage(
                    $chatId,
                    "✅ " .
                        count($parsedTransactions) .
                        " transaksi berhasil dicatat.\n\n" .

                        implode(
                            "\n",
                            $summary
                        ) .

                        "\n\n💳 Saldo Saat Ini\nRp " .

                        number_format(
                            $wallet->balance,
                            0,
                            ',',
                            '.'
                        ) .

                        "\n\n━━━━━━━━━━\n" .

                        "📋 Menu Cepat\n" .
                        "/saldo • /laporan • /help"
                );

                return response()->json([
                    'success' => true
                ]);
            } catch (\Exception $e) {

                DB::rollBack();

                Log::error(
                    'Gagal menyimpan transaksi Telegram: ' .
                        $e->getMessage(),
                    [
                        'trace' => $e->getTraceAsString(),
                        'chat_id' => $chatId,
                        'telegram_id' => $telegramId,
                    ]
                );

                $this->sendMessage(
                    $chatId,
                    "❌ Terjadi kesalahan saat menyimpan transaksi.\n\n" .
                        "Detail: " .
                        $e->getMessage()
                );

                return response()->json([
                    'success' => false
                ]);
            }
        }

        /**
         * COMMAND TIDAK DIKENAL
         */
        $this->sendMessage(
            $chatId,
            "❓ Perintah tidak dikenali.\n\n" .
                "Contoh:\n" .
                "+500000 Jual Logo\n" .
                "-25000 Beli Kopi\n" .
                "/start"
        );

        return response()->json([
            'success' => true
        ]);
    }

    private function isConnectionExpired(
        TelegramAccount $telegramAccount
    ): bool {

        if (!$telegramAccount->connected_at) {
            return true;
        }

        return $telegramAccount->connected_at->lt(
            now()->subDays(30)
        );
    }

    private function disconnectTelegram(
        TelegramAccount $telegramAccount
    ): void {

        $telegramAccount->update([
            'telegram_id' => null,
            'telegram_username' => null,
            'telegram_name' => null,
            'connected_at' => null,
        ]);
    }

    private function sendMessage(
        $chatId,
        $message,
        $replyMarkup = null
    ): void {

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        Http::withoutVerifying()
            ->post(
                'https://api.telegram.org/bot' .
                    env('TELEGRAM_BOT_TOKEN') .
                    '/sendMessage',
                $payload
            );
    }

    private function detectCategory(
        string $description,
        string $type
    ): string {

        $description = strtolower($description);

        /**
         * PEMASUKAN
         */
        if ($type === 'income') {

            if (
                str_contains($description, 'gaji')
            ) {
                return 'Gaji';
            }

            if (
                str_contains($description, 'affiliate') ||
                str_contains($description, 'komisi')
            ) {
                return 'Affiliate';
            }

            if (
                str_contains($description, 'jual') ||
                str_contains($description, 'penjualan')
            ) {
                return 'Penjualan';
            }

            if (
                str_contains($description, 'bonus')
            ) {
                return 'Bonus';
            }

            return 'Pemasukan Lainnya';
        }

        /**
         * PENGELUARAN — kategori diselaraskan dengan
         * label di sheet Dashboard (Makanan, Belanja,
         * Transport, Tagihan, Lainnya) agar chart top
         * pengeluaran bisa mengelompokkan data dengan benar.
         */
        if (
            str_contains($description, 'kopi') ||
            str_contains($description, 'makan') ||
            str_contains($description, 'minum') ||
            str_contains($description, 'restoran') ||
            str_contains($description, 'snack') ||
            str_contains($description, 'jajan')
        ) {
            return 'Makanan';
        }

        if (
            str_contains($description, 'bensin') ||
            str_contains($description, 'parkir') ||
            str_contains($description, 'tol') ||
            str_contains($description, 'grab') ||
            str_contains($description, 'gojek') ||
            str_contains($description, 'transport')
        ) {
            return 'Transport';
        }

        if (
            str_contains($description, 'listrik') ||
            str_contains($description, 'air') ||
            str_contains($description, 'internet') ||
            str_contains($description, 'wifi') ||
            str_contains($description, 'hosting') ||
            str_contains($description, 'domain') ||
            str_contains($description, 'pulsa') ||
            str_contains($description, 'token')
        ) {
            return 'Tagihan';
        }

        if (
            str_contains($description, 'belanja') ||
            str_contains($description, 'beli') ||
            str_contains($description, 'baju') ||
            str_contains($description, 'sepatu') ||
            str_contains($description, 'tas') ||
            str_contains($description, 'mart') ||
            str_contains($description, 'indomaret') ||
            str_contains($description, 'alfamart')
        ) {
            return 'Belanja';
        }

        if (
            str_contains($description, 'adobe') ||
            str_contains($description, 'chatgpt') ||
            str_contains($description, 'gemini') ||
            str_contains($description, 'canva') ||
            str_contains($description, 'netflix') ||
            str_contains($description, 'spotify') ||
            str_contains($description, 'youtube')
        ) {
            return 'Langganan';
        }

        if (
            str_contains($description, 'mouse') ||
            str_contains($description, 'keyboard') ||
            str_contains($description, 'monitor') ||
            str_contains($description, 'laptop')
        ) {
            return 'Peralatan';
        }

        return 'Lainnya';
    }
}
