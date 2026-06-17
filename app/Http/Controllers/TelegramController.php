<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __invoke(Request $request)
    {
        try {

            Log::info('Telegram Update', $request->all());

            $message = $request->input('message');

            if (!$message) {
                return response()->json([
                    'success' => true
                ]);
            }

            $telegramId = $message['from']['id'];
            $name = $message['from']['first_name'] ?? 'Unknown';
            $chatId = $message['chat']['id'];
            $text = trim($message['text'] ?? '');

            /**
             * Simpan user otomatis
             */
            $user = User::firstOrCreate(
                [
                    'telegram_id' => $telegramId
                ],
                [
                    'name' => $name
                ]
            );

            /**
             * Command Start
             */
            if ($text === '/start') {

                $this->sendMessage(
                    $chatId,
                    "👋 Selamat datang di Finance Bot\n\n" .
                        "Format transaksi:\n\n" .
                        "+500000 jual logo\n" .
                        "-25000 beli kopi"
                );

                return response()->json([
                    'success' => true
                ]);
            }

            /**
             * Parsing transaksi
             */
            if (
                preg_match(
                    '/^([+-])(\d+)\s(.+)$/',
                    $text,
                    $matches
                )
            ) {

                $symbol = $matches[1];
                $amount = (int) $matches[2];
                $description = trim($matches[3]);

                $type = $symbol === '+'
                    ? 'income'
                    : 'expense';

                Transaction::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $description,
                    'transaction_date' => now()
                ]);

                $saldo = $this->getBalance($user->id);

                $messageText =
                    "✅ Transaksi berhasil disimpan\n\n" .
                    "Tipe : " . ($type === 'income'
                        ? 'Pemasukan'
                        : 'Pengeluaran') . "\n" .
                    "Nominal : Rp " . number_format($amount, 0, ',', '.') . "\n" .
                    "Keterangan : " . $description . "\n\n" .
                    "Saldo Saat Ini : Rp " . number_format($saldo, 0, ',', '.');

                $this->sendMessage(
                    $chatId,
                    $messageText
                );

                return response()->json([
                    'success' => true
                ]);
            }

            /**
             * Command saldo
             */
            if ($text === '/saldo') {

                $saldo = $this->getBalance($user->id);

                $this->sendMessage(
                    $chatId,
                    "💰 Saldo Saat Ini\n\nRp " . number_format(
                        $saldo,
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
             * Command laporan
             */
            if ($text === '/laporan') {

                $income = Transaction::where(
                    'user_id',
                    $user->id
                )
                    ->where('type', 'income')
                    ->sum('amount');

                $expense = Transaction::where(
                    'user_id',
                    $user->id
                )
                    ->where('type', 'expense')
                    ->sum('amount');

                $this->sendMessage(
                    $chatId,
                    "📊 Laporan Keuangan\n\n" .
                        "Pemasukan : Rp " . number_format(
                            $income,
                            0,
                            ',',
                            '.'
                        ) . "\n" .
                        "Pengeluaran : Rp " . number_format(
                            $expense,
                            0,
                            ',',
                            '.'
                        ) . "\n" .
                        "Saldo : Rp " . number_format(
                            $income - $expense,
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
             * Format tidak valid
             */
            $this->sendMessage(
                $chatId,
                "❌ Format tidak dikenali\n\n" .
                    "Contoh:\n" .
                    "+500000 jual logo\n" .
                    "-25000 beli kopi\n\n" .
                    "Perintah:\n" .
                    "/saldo\n" .
                    "/laporan"
            );

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
