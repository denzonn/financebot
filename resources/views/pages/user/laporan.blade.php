@extends('layouts.app')

@section('title', 'Laporan')
@section('page_title', 'Laporan')

@section('content')

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="overflow-hidden rounded-[32px] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 p-6 text-white shadow-xl">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sky-100">
                        Financial Health Score
                    </p>

                    <div class="mt-2 flex items-end gap-2">

                        <h2 class="text-5xl font-black">

                            {{ $score }}

                        </h2>

                        <span class="mb-2 text-sky-100">
                            /100
                        </span>

                    </div>

                    <p class="mt-3 font-medium">

                        {{ $status }}

                    </p>

                </div>

                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/10 backdrop-blur">

                    <i class="fa-solid fa-chart-line text-3xl"></i>

                </div>

            </div>

        </div>

        {{-- CASH FLOW --}}
        <div class="grid gap-4 lg:grid-cols-3">

            <div class="rounded-3xl bg-white p-5 shadow-sm lg:col-span-2">

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-sm text-slate-500">
                            Saldo Saat Ini
                        </p>

                        <h2 class="mt-2 text-3xl font-black text-sky-600">

                            Rp {{ number_format($saldo, 0, ',', '.') }}

                        </h2>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">

                        <i class="fa-solid fa-wallet"></i>

                    </div>

                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">

                    <div class="rounded-2xl bg-green-50 p-4">

                        <p class="text-xs text-green-600">
                            Pemasukan
                        </p>

                        <h4 class="mt-1 font-bold text-green-600">

                            Rp {{ number_format($totalIncome, 0, ',', '.') }}

                        </h4>

                    </div>

                    <div class="rounded-2xl bg-red-50 p-4">

                        <p class="text-xs text-red-600">
                            Pengeluaran
                        </p>

                        <h4 class="mt-1 font-bold text-red-600">

                            Rp {{ number_format($totalExpense, 0, ',', '.') }}

                        </h4>

                    </div>

                </div>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <p class="text-sm text-slate-500">
                    Total Transaksi
                </p>

                <h2 class="mt-2 text-4xl font-black text-slate-800">

                    {{ number_format($totalTransaction) }}

                </h2>

                <p class="mt-2 text-sm text-slate-400">

                    Transaksi tercatat

                </p>

            </div>

        </div>

        {{-- GRAFIK --}}
        <div class="rounded-3xl bg-white p-5 shadow-sm">

            <div class="mb-5">

                <h3 class="font-bold">

                    Tren Keuangan 6 Bulan

                </h3>

                <p class="text-sm text-slate-500">

                    Perkembangan cash flow bersih

                </p>

            </div>

            <div class="h-[300px]">

                <canvas id="financeChart"></canvas>

            </div>

        </div>

        {{-- INSIGHT --}}
        <div class="rounded-3xl border border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 p-5">

            <div class="flex items-start gap-4">

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">

                    <i class="fa-solid fa-lightbulb"></i>

                </div>

                <div>

                    <h3 class="font-bold text-amber-700">

                        Insight FinanceBot

                    </h3>

                    <p class="mt-2 text-sm text-slate-600">

                        @if ($totalIncome > $totalExpense)
                            Keuangan Anda masih sehat karena pemasukan lebih besar daripada pengeluaran.
                        @else
                            Pengeluaran Anda sudah melebihi pemasukan. Sebaiknya kurangi pengeluaran yang tidak penting.
                        @endif

                    </p>

                </div>

            </div>

        </div>

        {{-- KATEGORI + AKTIVITAS --}}
        <div class="grid gap-4 lg:grid-cols-2">

            {{-- KATEGORI --}}
            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <h3 class="mb-5 font-bold">

                    Pengeluaran Terbesar

                </h3>

                @php
                    $grandTotal = $topCategories->sum('total');
                @endphp

                <div class="space-y-5">

                    @forelse($topCategories as $category)
                        @php
                            $percent = $grandTotal > 0 ? ($category->total / $grandTotal) * 100 : 0;
                        @endphp

                        <div>

                            <div class="mb-2 flex justify-between">

                                <span>

                                    {{ $category->category ?: 'Lainnya' }}

                                </span>

                                <span class="font-semibold">

                                    Rp {{ number_format($category->total, 0, ',', '.') }}

                                </span>

                            </div>

                            <div class="h-3 rounded-full bg-slate-100">

                                <div class="h-3 rounded-full bg-sky-500" style="width: {{ $percent }}%"></div>

                            </div>

                        </div>

                    @empty

                        <div class="text-center text-slate-500">

                            Belum ada data kategori

                        </div>
                    @endforelse

                </div>

            </div>

            {{-- AKTIVITAS --}}
            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <h3 class="mb-5 font-bold">

                    Aktivitas Terakhir

                </h3>

                <div class="space-y-4">

                    @forelse($recentTransactions as $trx)
                        <div class="flex items-center justify-between">

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

                        <div class="text-center text-slate-500">

                            Belum ada transaksi

                        </div>
                    @endforelse

                </div>

            </div>

        </div>

    </div>

@endsection

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        new Chart(
            document.getElementById(
                'financeChart'
            ), {
                type: 'line',

                data: {

                    labels: @json($months),

                    datasets: [{
                        label: 'Cash Flow',
                        data: @json($netFlow),
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2,132,199,.10)',
                        fill: true,
                        tension: .4
                    }]
                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {
                            display: false
                        }
                    }
                }
            }
        );
    </script>
@endpush
