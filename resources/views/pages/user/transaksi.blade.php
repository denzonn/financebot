@extends('layouts.app')

@section('title', 'Transaksi')
@section('page_title', 'Transaksi')

@section('content')

    <div class="space-y-6">

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">Saldo Saat Ini</p>
                <h3 class="text-2xl font-bold text-blue-600">
                    Rp {{ number_format($saldo, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">Pemasukan Bulan Ini</p>
                <h3 class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
                </h3>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">Pengeluaran Bulan Ini</p>
                <h3 class="text-2xl font-bold text-red-600">
                    Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                </h3>
            </div>

        </div>

        {{-- Filter --}}
        <form method="GET" class="flex flex-col lg:flex-row gap-3">

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori atau catatan..."
                class="w-full border rounded-lg px-4 py-2">

            <select name="type" class="border rounded-lg px-4 py-2">

                <option value="">Semua Jenis</option>
                <option value="income" @selected(request('type') == 'income')>
                    Pemasukan
                </option>
                <option value="expense" @selected(request('type') == 'expense')>
                    Pengeluaran
                </option>

            </select>

            <input type="text" id="daterange" name="daterange" value="{{ request('daterange') }}"
                placeholder="Pilih rentang tanggal" class="border rounded-lg px-4 py-2 min-w-[260px]">

            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                Filter
            </button>

        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

            <div class="px-5 py-4 border-b">
                <h3 class="font-semibold">
                    Riwayat Transaksi
                </h3>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Jenis</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($transactions as $trx)
                            <tr class="border-b">

                                <td class="px-4 py-3">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3">

                                    @if ($trx->type == 'income')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                                            Pengeluaran
                                        </span>
                                    @endif

                                </td>

                                <td class="px-4 py-3">
                                    {{ $trx->category }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $trx->description ?: '-' }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">

                                    @if ($trx->type == 'income')
                                        <span class="text-green-600">
                                            +Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-red-600">
                                            -Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">
                                    Belum ada transaksi
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>

                <div class="p-4 border-t">
                    {{ $transactions->links() }}
                </div>
            </div>

        </div>

    </div>

@endsection

@push('addon-script')
    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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
    </script>
@endpush
