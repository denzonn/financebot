<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $wallet = Wallet::where(
            'user_id',
            $userId
        )->first();

        $saldo = $wallet?->balance ?? 0;

        $totalIncome = Transaction::where(
            'user_id',
            $userId
        )
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = Transaction::where(
            'user_id',
            $userId
        )
            ->where('type', 'expense')
            ->sum('amount');

        $totalTransaction = Transaction::where(
            'user_id',
            $userId
        )->count();

        $score = 50;

        if ($totalIncome > 0) {

            $score = min(
                100,
                round(
                    ($totalIncome /
                        max($totalExpense, 1))
                        * 50
                )
            );
        }

        $status = match (true) {
            $score >= 80 => 'Sangat Baik',
            $score >= 60 => 'Baik',
            $score >= 40 => 'Cukup',
            default => 'Perlu Perbaikan'
        };

        $months = [];
        $netFlow = [];

        for ($i = 5; $i >= 0; $i--) {

            $date = now()->subMonths($i);

            $months[] =
                $date->translatedFormat('M');

            $income = Transaction::where(
                'user_id',
                $userId
            )
                ->where('type', 'income')
                ->whereYear(
                    'transaction_date',
                    $date->year
                )
                ->whereMonth(
                    'transaction_date',
                    $date->month
                )
                ->sum('amount');

            $expense = Transaction::where(
                'user_id',
                $userId
            )
                ->where('type', 'expense')
                ->whereYear(
                    'transaction_date',
                    $date->year
                )
                ->whereMonth(
                    'transaction_date',
                    $date->month
                )
                ->sum('amount');

            $netFlow[] =
                $income - $expense;
        }

        $topCategories = Transaction::select(
            'category',
            DB::raw('SUM(amount) as total')
        )
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'type',
                'expense'
            )
            ->whereNotNull(
                'category'
            )
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $recentTransactions =
            Transaction::where(
                'user_id',
                $userId
            )
            ->latest(
                'transaction_date'
            )
            ->limit(5)
            ->get();

        return view(
            'pages.user.laporan',
            compact(
                'saldo',
                'totalIncome',
                'totalExpense',
                'totalTransaction',
                'score',
                'status',
                'months',
                'netFlow',
                'topCategories',
                'recentTransactions'
            )
        );
    }
}
