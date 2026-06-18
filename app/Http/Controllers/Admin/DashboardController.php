<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $wallet = Wallet::where(
            'user_id',
            $user->id
        )->first();

        $saldo =
            $wallet?->balance ?? 0;

        $totalIncome =
            Transaction::where(
                'user_id',
                $user->id
            )
            ->where(
                'type',
                'income'
            )
            ->sum('amount');

        $totalExpense =
            Transaction::where(
                'user_id',
                $user->id
            )
            ->where(
                'type',
                'expense'
            )
            ->sum('amount');

        $totalTransaction =
            Transaction::where(
                'user_id',
                $user->id
            )->count();

        $recentTransactions =
            Transaction::where(
                'user_id',
                $user->id
            )
            ->latest(
                'transaction_date'
            )
            ->limit(5)
            ->get();

        $telegramAccount =
            $user->telegramAccount;

        $showTelegramModal =
            !$telegramAccount ||
            !$telegramAccount->telegram_id;

        return view(
            'pages.user.dashboard',
            compact(
                'saldo',
                'totalIncome',
                'totalExpense',
                'totalTransaction',
                'recentTransactions',
                'telegramAccount',
                'showTelegramModal'
            )
        );
    }
}
