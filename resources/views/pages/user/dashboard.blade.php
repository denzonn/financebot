@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="overflow-hidden rounded-[32px] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 p-6 text-white shadow-xl">

            <div class="flex items-start justify-between">

                <div>

                    <p class="text-sky-100">
                        Halo, {{ auth()->user()->name }} 👋
                    </p>

                    <h2 class="mt-3 text-3xl md:text-4xl font-black">

                        Rp {{ number_format($saldo, 0, ',', '.') }}

                    </h2>

                    <p class="mt-3 text-sky-100">

                        @if ($saldo > 0)
                            Kondisi keuangan Anda cukup baik.
                        @else
                            Mulai catat transaksi pertama Anda.
                        @endif

                    </p>

                </div>

                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/10">

                    <i class="fa-solid fa-wallet text-2xl"></i>

                </div>

            </div>

        </div>

        {{-- QUICK ACTION --}}
        <div class="grid grid-cols-2 gap-4">

            <a href="{{ route('user.bot') }}" class="rounded-3xl bg-white p-5 shadow-sm">

                <i class="fa-brands fa-telegram text-2xl text-sky-500"></i>

                <h3 class="mt-3 font-semibold">
                    Telegram
                </h3>

                <p class="text-sm text-slate-500">
                    Hubungkan Bot
                </p>

            </a>

            <a href="{{ route('user.transaksi') }}" class="rounded-3xl bg-white p-5 shadow-sm">

                <i class="fa-solid fa-receipt text-2xl text-emerald-500"></i>

                <h3 class="mt-3 font-semibold">
                    Transaksi
                </h3>

                <p class="text-sm text-slate-500">
                    Kelola transaksi
                </p>

            </a>

            <a href="{{ route('user.laporan') }}" class="rounded-3xl bg-white p-5 shadow-sm">

                <i class="fa-solid fa-chart-line text-2xl text-indigo-500"></i>

                <h3 class="mt-3 font-semibold">
                    Laporan
                </h3>

                <p class="text-sm text-slate-500">
                    Analisis keuangan
                </p>

            </a>

            <a href="{{ route('user.profile') }}" class="rounded-3xl bg-white p-5 shadow-sm">

                <i class="fa-solid fa-user text-2xl text-amber-500"></i>

                <h3 class="mt-3 font-semibold">
                    Akun
                </h3>

                <p class="text-sm text-slate-500">
                    Pengaturan akun
                </p>

            </a>

        </div>

        {{-- INSIGHT --}}
        <div class="rounded-3xl border border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 p-5">

            <div class="flex gap-4">

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">

                    <i class="fa-solid fa-lightbulb"></i>

                </div>

                <div>

                    <h3 class="font-bold text-amber-700">
                        Insight Hari Ini
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">

                        @if ($totalIncome > $totalExpense)
                            Pemasukan Anda masih lebih besar dari pengeluaran.
                        @else
                            Pengeluaran sudah melebihi pemasukan. Perlu perhatian.
                        @endif

                    </p>

                </div>

            </div>

        </div>

        {{-- TELEGRAM STATUS --}}
        <div class="rounded-3xl bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h3 class="font-bold">
                        Telegram Bot
                    </h3>

                    <p class="text-sm text-slate-500">

                        @if ($telegramAccount?->telegram_id)
                            Terhubung dengan Telegram
                        @else
                            Belum terhubung
                        @endif

                    </p>

                </div>

                @if ($telegramAccount?->telegram_id)
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-600">

                        Connected

                    </span>
                @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-600">

                        Not Connected

                    </span>
                @endif

            </div>

        </div>

        {{-- TRANSAKSI TERAKHIR --}}
        <div class="rounded-3xl bg-white p-5 shadow-sm">

            <div class="mb-4 flex items-center justify-between">

                <h3 class="font-bold">
                    Aktivitas Terbaru
                </h3>

                <a href="{{ route('user.transaksi') }}" class="text-sm text-sky-600">

                    Lihat Semua

                </a>

            </div>

            <div class="space-y-3">

                @forelse($recentTransactions as $trx)
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">

                        <div>

                            <h4 class="font-medium">

                                {{ $trx->description ?: 'Transaksi' }}

                            </h4>

                            <p class="text-xs text-slate-500">

                                {{ \Carbon\Carbon::parse($trx->transaction_date)->diffForHumans() }}

                            </p>

                        </div>

                        <span class="{{ $trx->type == 'income' ? 'text-green-600' : 'text-red-600' }} font-bold">

                            {{ $trx->type == 'income' ? '+' : '-' }}
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}

                        </span>

                    </div>

                @empty

                    <div class="py-8 text-center text-slate-500">

                        Belum ada transaksi

                    </div>
                @endforelse

            </div>

        </div>

    </div>
@endsection
