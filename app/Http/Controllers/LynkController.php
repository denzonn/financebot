<?php

namespace App\Http\Controllers;

use App\Mail\FinanceBotExistingMail;
use App\Mail\FinanceBotWelcomeMail;
use App\Models\TelegramAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LynkController extends Controller
{
    public function handle(Request $request)
    {
        try {
            if ($request->event !== 'payment.received') {

                return response()->json([
                    'success' => true,
                    'message' => 'Event ignored'
                ]);
            }

            $email = data_get(
                $request->all(),
                'data.message_data.customer.email'
            );

            $name = data_get(
                $request->all(),
                'data.message_data.customer.name'
            );

            $phone = data_get(
                $request->all(),
                'data.message_data.customer.phone'
            );

            $productTitle = data_get(
                $request->all(),
                'data.message_data.items.0.title'
            );

            $questions = data_get(
                $request->all(),
                'data.message_data.items.0.questions'
            );

            $questions = json_decode($questions, true);

            $password = $questions['Password'] ?? null;

            /**
             * Reference transaksi
             */
            $refId = data_get(
                $request->all(),
                'data.message_data.refId'
            );

            /**
             * Filter hanya FinanceBot
             */
            $allowedProducts = [
                'FinanceBot',
                'FinanceBot Premium',
                'FinanceBot SaaS'
            ];

            if (!in_array($productTitle, $allowedProducts)) {

                return response()->json([
                    'success' => true,
                    'message' => 'Product ignored'
                ]);
            }

            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {

                $waNumber = '6282396493775';

                $waMessage = urlencode(
                    "Halo Admin FinanceBot,\n\n" .
                        "Saya sudah melakukan pembayaran namun email saya sudah terdaftar.\n\n" .
                        "Email: {$email}\n" .
                        "Nama: {$name}\n" .
                        "Ref ID: {$refId}"
                );

                $waUrl = "https://wa.me/{$waNumber}?text={$waMessage}";

                Mail::to($email)
                    ->send(
                        new FinanceBotExistingMail(
                            $name,
                            $email,
                            $waUrl
                        )
                    );

                return response()->json([
                    'success' => true,
                    'message' => 'User already exists'
                ]);
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($password),
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);

            TelegramAccount::create([
                'user_id' => $user->id
            ]);

            Mail::to($email)
                ->send(
                    new FinanceBotWelcomeMail(
                        $user,
                        $password
                    )
                );

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
