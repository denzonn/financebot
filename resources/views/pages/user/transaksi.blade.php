@extends('layouts.app')

@section('title', 'Transaksi')
@section('page_title', 'Transaksi')

@section('content')

    <div class="space-y-6">

        {{-- Statistik --}}
        <div class="grid gap-4 lg:grid-cols-3">

            <div class="rounded-3xl bg-gradient-to-r from-sky-600 to-indigo-600 p-6 text-white shadow-xl">

                <p class="text-sky-100 text-sm">
                    Saldo Saat Ini
                </p>

                <h2 class="mt-3 text-3xl font-black">
                    Rp {{ number_format($saldo, 0, ',', '.') }}
                </h2>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-600">

                        <i class="fa-solid fa-arrow-trend-up"></i>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">
                            Pemasukan
                        </p>

                        <h3 class="font-bold text-green-600">
                            Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-600">

                        <i class="fa-solid fa-arrow-trend-down"></i>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">
                            Pengeluaran
                        </p>

                        <h3 class="font-bold text-red-600">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </h3>

                    </div>

                </div>

            </div>

        </div>

        {{-- Filter --}}
        <div class="rounded-[28px] bg-white p-5 shadow-sm">

            <div class="mb-5 flex items-center justify-between">

                <div>

                    <h3 class="font-bold text-slate-900">
                        Filter Transaksi
                    </h3>

                    <p class="text-sm text-slate-500">
                        Cari dan filter riwayat transaksi
                    </p>

                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">

                    <i class="fa-solid fa-sliders"></i>

                </div>

            </div>

            <form method="GET" class="space-y-4">

                {{-- Search --}}
                <div class="relative">

                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari transaksi..."
                        class="w-full rounded-2xl border-0 bg-slate-100 py-3 pl-11 pr-4 focus:ring-2 focus:ring-sky-500">

                </div>

                {{-- Filter Buttons --}}
                <div class="grid grid-cols-2 gap-3">

                    <select name="type" class="rounded-2xl border-0 bg-slate-100 px-4 py-3">

                        <option value="">
                            Semua Jenis
                        </option>

                        <option value="income" @selected(request('type') == 'income')>

                            💰 Pemasukan

                        </option>

                        <option value="expense" @selected(request('type') == 'expense')>

                            💸 Pengeluaran

                        </option>

                    </select>

                    <div class="relative">

                        <i class="fa-regular fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                        <input type="text" id="daterange" name="daterange" value="{{ request('daterange') }}"
                            placeholder="Pilih tanggal" class="w-full rounded-2xl border-0 bg-slate-100 py-3 pl-11 pr-4">

                    </div>

                </div>

                {{-- Buttons --}}
                <div class="grid grid-cols-2 gap-3">

                    <button
                        class="rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 py-3 font-medium text-white shadow-lg">

                        <i class="fa-solid fa-filter mr-2"></i>

                        Terapkan

                    </button>

                    <a href="{{ route('user.transaksi') }}"
                        class="flex items-center justify-center rounded-2xl bg-slate-100 py-3 font-medium text-slate-600">

                        <i class="fa-solid fa-rotate-left mr-2"></i>

                        Reset

                    </a>

                </div>

            </form>

        </div>

        <div class="flex items-center justify-between">

            <div>

                <h3 class="font-bold text-slate-900">
                    Riwayat Transaksi
                </h3>

                <p class="text-sm text-slate-500">

                    {{ $transactions->total() }} transaksi ditemukan

                </p>

            </div>

        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3">

            <div class="flex items-start gap-3">

                <i class="fa-solid fa-circle-info mt-0.5 text-amber-500"></i>

                <p class="text-sm text-amber-700">

                    Transaksi hanya dapat dihapus maksimal
                    <b>24 jam</b> setelah dibuat.

                </p>

            </div>

        </div>

        {{-- Tabel --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @forelse ($transactions as $trx)
                <div
                    class="rounded-[28px] border border-slate-100 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-lg">

                    <div class="flex items-start gap-3">

                        {{-- ICON --}}
                        <div
                            class="flex h-9 w-9 md:h-12 md:w-12 shrink-0 items-center justify-center rounded-lg md:rounded-2xl
                    {{ $trx->type == 'income' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">

                            <i
                                class="fa-solid text-xs md:text-base
                        {{ $trx->type == 'income' ? 'fa-plus' : 'fa-minus' }}">
                            </i>

                        </div>

                        {{-- CONTENT --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-start justify-between gap-3">

                                <div class="min-w-0 flex-1">

                                    <h4 class="truncate font-semibold text-slate-800">

                                        {{ $trx->description ?: 'Transaksi' }}

                                    </h4>

                                    <p class="mt-1 text-xs text-slate-500">

                                        {{ $trx->category ?: 'Lainnya' }}

                                        •

                                        {{ \Carbon\Carbon::parse($trx->transaction_date)->diffForHumans() }}

                                    </p>


                                </div>

                                @if (\Carbon\Carbon::parse($trx->transaction_date)->gte(now()->subDay()->startOfDay()))
                                    <button type="button" data-id="{{ $trx->id }}"
                                        class="btn-delete flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-red-50 hover:text-red-500">

                                        <i class="fa-solid fa-trash text-xs md:text-base"></i>

                                    </button>

                                    <form id="deleteForm{{ $trx->id }}"
                                        action="{{ route('user.transaksi.destroy', $trx->id) }}" method="POST"
                                        class="hidden">

                                        @csrf
                                        @method('DELETE')

                                    </form>
                                @endif

                            </div>

                            {{-- NOMINAL --}}
                            <div class="mt-4">

                                <h3
                                    class="text-lg font-black
                            {{ $trx->type == 'income' ? 'text-green-600' : 'text-red-600' }}">

                                    {{ $trx->type == 'income' ? '+' : '-' }}

                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}

                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="col-span-full">

                    <div class="rounded-3xl bg-white p-12 text-center shadow-sm">

                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">

                            <i class="fa-solid fa-wallet text-2xl"></i>

                        </div>

                        <h3 class="font-semibold text-slate-800">

                            Belum Ada Transaksi

                        </h3>

                        <p class="mt-2 text-sm text-slate-500">

                            Transaksi dari Telegram akan muncul di sini.

                        </p>

                    </div>

                </div>
            @endforelse

        </div>

        @if ($transactions->hasPages())
            <div class="rounded-3xl bg-white p-4 shadow-sm">

                {{ $transactions->links() }}

            </div>
        @endif
    </div>

@endsection

@push('addon-script')
    {{-- jquery --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Clear'
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('DD/MM/YYYY') +
                ' - ' +
                picker.endDate.format('DD/MM/YYYY')
            );
        });

        $(document).on(
            'click',
            '.btn-delete',
            function() {

                let id =
                    $(this).data('id');

                Swal.fire({
                    title: 'Hapus transaksi?',
                    text: 'Saldo akan disesuaikan kembali',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $('#deleteForm' + id)
                            .submit();

                    }

                });

            }
        );

        function toggleMenu(id) {
            document
                .querySelectorAll('[id^="menu"]')
                .forEach(menu => {

                    if (menu.id !== 'menu' + id) {
                        menu.classList.add('hidden');
                    }

                });

            document
                .getElementById('menu' + id)
                .classList
                .toggle('hidden');
        }

        document.addEventListener('click', function(e) {

            if (
                !e.target.closest('[onclick^="toggleMenu"]') &&
                !e.target.closest('[id^="menu"]')
            ) {

                document
                    .querySelectorAll('[id^="menu"]')
                    .forEach(menu => {

                        menu.classList.add('hidden');

                    });

            }

        });
    </script>
@endpush
