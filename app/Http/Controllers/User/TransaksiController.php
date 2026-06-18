<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Transaction::where('user_id', $userId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('category', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('daterange')) {

            $range = explode(' - ', $request->daterange);

            if (count($range) == 2) {

                $start = Carbon::createFromFormat('d/m/Y', trim($range[0]))->startOfDay();
                $end = Carbon::createFromFormat('d/m/Y', trim($range[1]))->endOfDay();

                $query->whereBetween('transaction_date', [$start, $end]);
            }
        }

        $transactions = $query
            ->latest('transaction_date')
            ->paginate(20)
            ->withQueryString();

        $wallet = Wallet::where('user_id', $userId)->first();

        $saldo = $wallet?->balance ?? 0;

        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $totalPemasukan = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$awalBulan, $akhirBulan])
            ->sum('amount');

        $totalPengeluaran = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$awalBulan, $akhirBulan])
            ->sum('amount');

        return view('pages.user.transaksi', compact(
            'transactions',
            'saldo',
            'totalPemasukan',
            'totalPengeluaran'
        ));
    }

    public function destroy(
        Transaction $transaction
    ) {

        abort_if(
            $transaction->user_id !== auth()->id(),
            403
        );

        $trxDate = Carbon::parse(
            $transaction->transaction_date
        );

        if (
            $trxDate->lt(
                now()->subDay()->startOfDay()
            )
        ) {

            return back()->with(
                'error',
                'Transaksi hanya dapat dihapus maksimal 1 hari ke belakang.'
            );
        }

        $wallet = Wallet::firstOrCreate(
            [
                'user_id' => auth()->id()
            ],
            [
                'balance' => 0
            ]
        );

        if (
            $transaction->type === 'income'
        ) {

            $wallet->decrement(
                'balance',
                $transaction->amount
            );
        } else {

            $wallet->increment(
                'balance',
                $transaction->amount
            );
        }

        $transaction->delete();

        return back()->with(
            'toast_success',
            'Transaksi berhasil dihapus'
        );
    }
}
