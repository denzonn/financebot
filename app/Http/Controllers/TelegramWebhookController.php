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
        Log::info($request->all());

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
                "✅ Telegram berhasil terhubung.\n\n" .
                    "Contoh:\n" .
                    "+500000 Jual Logo\n" .
                    "-25000 Beli Kopi"
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
             * Telegram sudah pernah dipakai?
             */
            $alreadyConnected =
                TelegramAccount::where(
                    'telegram_id',
                    $telegramId
                )->first();

            if ($alreadyConnected) {

                $this->sendMessage(
                    $chatId,
                    "⚠️ Akun Telegram ini sudah terhubung ke FinanceBot."
                );

                return response()->json([
                    'success' => true
                ]);
            }

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

            $telegramAccount->update([
                'telegram_id' => $telegramId,
                'telegram_username' => $username,
                'telegram_name' => $fullName,
                'connected_at' => now(),
                'connect_code' => null,
            ]);

            $this->sendMessage(
                $chatId,
                "🎉 Telegram berhasil terhubung.\n\n" .
                    "Sekarang Anda dapat mencatat transaksi.\n\n" .
                    "Contoh:\n" .
                    "+500000 Jual Logo\n" .
                    "-25000 Beli Kopi"
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
         * TRANSAKSI
         */
        if (
            preg_match(
                '/^([+-])([\d\.]+)\s*(.*)$/',
                $text,
                $matches
            )
        ) {

            DB::beginTransaction();

            try {

                $userId = $telegramAccount->user_id;

                $type = $matches[1] == '+'
                    ? 'income'
                    : 'expense';

                $amount = (float) str_replace(
                    '.',
                    '',
                    $matches[2]
                );

                $description = trim(
                    $matches[3] ?? ''
                );

                $wallet = Wallet::firstOrCreate(
                    [
                        'user_id' => $userId
                    ],
                    [
                        'balance' => 0
                    ]
                );

                /**
                 * Cek saldo untuk pengeluaran
                 */
                if ($type === 'expense') {

                    if ($wallet->balance <= 0) {

                        $this->sendMessage(
                            $chatId,
                            "❌ Saldo Anda kosong.\n\n" .
                                "Tidak dapat mencatat pengeluaran."
                        );

                        return response()->json([
                            'success' => true
                        ]);
                    }

                    if ($wallet->balance < $amount) {

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

                /**
                 * Simpan transaksi
                 */
                Transaction::create([
                    'user_id' => $userId,
                    'category' => null,
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $description,
                    'receipt_photo' => null,
                    'transaction_date' => now(),
                ]);

                /**
                 * Update saldo
                 */
                if ($type === 'income') {

                    $wallet->increment(
                        'balance',
                        $amount
                    );
                } else {

                    $wallet->decrement(
                        'balance',
                        $amount
                    );
                }

                $wallet->refresh();

                $telegramAccount->update([
                    'connected_at' => now()
                ]);

                DB::commit();

                $this->sendMessage(
                    $chatId,
                    ($type === 'income'
                        ? "💰 PEMASUKAN BERHASIL\n\n"
                        : "💸 PENGELUARAN BERHASIL\n\n") .

                        "Jumlah: Rp " .
                        number_format(
                            $amount,
                            0,
                            ',',
                            '.'
                        ) .

                        "\nKeterangan: " .
                        ($description ?: '-') .

                        "\n\n💳 Saldo Saat Ini:\nRp " .
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
            } catch (\Exception $e) {

                DB::rollBack();

                Log::error(
                    'TELEGRAM TRANSACTION ERROR: ' .
                        $e->getMessage()
                );

                $this->sendMessage(
                    $chatId,
                    "❌ Terjadi kesalahan saat menyimpan transaksi."
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
        $message
    ): void {

        Http::withoutVerifying()
            ->post(
                'https://api.telegram.org/bot' .
                    env('TELEGRAM_BOT_TOKEN') .
                    '/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]
            );
    }
}
