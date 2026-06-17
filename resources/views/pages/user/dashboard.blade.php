@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    <div class="space-y-6">

        <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 to-indigo-600 p-5 text-white shadow-xl">

            <div class="flex items-center justify-between">

                <p class="text-sm text-sky-100">
                    Total Saldo
                </p>

                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1.5 text-xs">

                    <span class="h-2 w-2 rounded-full bg-emerald-400">
                    </span>

                    Connected

                </span>

            </div>

            <h2 class="mt-4 text-3xl font-black leading-none sm:text-5xl">

                Rp 12.500.000

            </h2>

            <div class="mt-5 flex items-center gap-2 text-sm text-sky-100">

                <i class="fa-brands fa-telegram"></i>

                <span>
                    Sinkron otomatis dari Telegram Bot
                </span>

            </div>

        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    Pemasukan
                </p>

                <h3 class="mt-2 text-xl font-bold text-emerald-500">
                    Rp 8.200.000
                </h3>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    Pengeluaran
                </p>

                <h3 class="mt-2 text-xl font-bold text-red-500">
                    Rp 1.700.000
                </h3>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    Total Transaksi
                </p>

                <h3 class="mt-2 text-xl font-bold">
                    134
                </h3>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    Bulan Ini
                </p>

                <h3 class="mt-2 text-xl font-bold">
                    Juni 2026
                </h3>

            </div>

        </div>

        <div class="rounded-3xl border border-sky-100 bg-sky-50 p-6">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-600 text-white">

                    <i class="fa-brands fa-telegram"></i>

                </div>

                <div>

                    <h3 class="font-semibold text-sky-700">
                        Cara Mencatat Transaksi
                    </h3>

                    <div class="mt-3 space-y-2 text-sm">

                        <div>
                            ➕ +500000 Jual Logo
                        </div>

                        <div>
                            ➖ -25000 Beli Kopi
                        </div>

                        <div>
                            📷 Upload foto nota
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="rounded-3xl bg-white p-5 shadow-sm">

            <div class="mb-4 flex items-center justify-between">

                <h3 class="font-semibold">
                    Transaksi Terbaru
                </h3>

                <a href="#" class="text-sky-600">
                    Lihat Semua
                </a>

            </div>

            <div class="space-y-3">

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">

                    <div>

                        <h4 class="font-medium">
                            Jual Logo
                        </h4>

                        <p class="text-sm text-slate-500">
                            Hari Ini
                        </p>

                    </div>

                    <span class="font-semibold text-emerald-500">
                        +Rp500.000
                    </span>

                </div>

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">

                    <div>

                        <h4 class="font-medium">
                            Beli Kopi
                        </h4>

                        <p class="text-sm text-slate-500">
                            Hari Ini
                        </p>

                    </div>

                    <span class="font-semibold text-red-500">
                        -Rp25.000
                    </span>

                </div>

            </div>

        </div>

    </div>

@endsection
