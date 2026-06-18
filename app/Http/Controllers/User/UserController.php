<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $wallet = $user->wallet;

        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = $user->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        return view(
            'pages.user.profile',
            compact(
                'user',
                'wallet',
                'totalIncome',
                'totalExpense'
            )
        );
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required'
            ],
            'password' => [
                'required',
                'confirmed',
                'min:6'
            ],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = auth()->user();

        if (
            !Hash::check(
                $request->current_password,
                $user->password
            )
        ) {

            return back()->with(
                'toast_error',
                'Password saat ini tidak sesuai.'
            );
        }

        if (
            Hash::check(
                $request->password,
                $user->password
            )
        ) {

            return back()->with(
                'toast_error',
                'Password baru tidak boleh sama dengan password lama.'
            );
        }

        $user->update([
            'password' => bcrypt(
                $request->password
            )
        ]);

        return back()->with(
            'toast_success',
            'Password berhasil diperbarui.'
        );
    }
}
