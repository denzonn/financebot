@extends('layouts.auth')

@section('title')
    Login - FinanceBot
@endsection

@section('content')
    <div class="min-h-screen bg-slate-50">

        <div class="grid min-h-screen lg:grid-cols-2">

            <!-- LEFT SIDE -->
            <div
                class="hidden lg:flex flex-col justify-between bg-gradient-to-br from-sky-700 via-sky-600 to-indigo-700 p-12 text-white">

                <div>

                    <div class="flex items-center gap-3">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white font-bold text-sky-700">
                            <img src="{{ asset('images/Logo.png') }}" alt="FinanceBot Logo" class="h-11 w-11 object-contain">
                        </div>

                        <div>

                            <h1 class="text-xl font-bold">
                                FinanceBot
                            </h1>

                            <p class="text-xs text-sky-100">
                                Telegram Finance Assistant
                            </p>

                        </div>

                    </div>

                </div>

                <div class="max-w-lg">

                    <span
                        class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm backdrop-blur">

                        🚀 Catat Keuangan Langsung Dari Telegram

                    </span>

                    <h2 class="mt-8 text-5xl font-black leading-tight">

                        Keuangan Bisnis

                        <br>

                        Semudah Chat.

                    </h2>

                    <p class="mt-6 text-lg leading-8 text-sky-100">

                        Kirim pemasukan, pengeluaran,
                        atau upload foto nota langsung dari Telegram.

                        Semua transaksi otomatis masuk dashboard.

                    </p>

                    <div class="mt-10 rounded-3xl border border-white/20 bg-white/10 p-5 shadow-2xl backdrop-blur-xl">

                        <div class="space-y-3 text-sm">

                            <div class="ml-auto w-fit rounded-2xl bg-white px-4 py-2 text-sky-700">

                                +500000 jual logo

                            </div>

                            <div class="w-fit rounded-2xl bg-white/10 px-4 py-2">

                                ✅ Transaksi berhasil disimpan

                            </div>

                            <div class="ml-auto w-fit rounded-2xl bg-white px-4 py-2 text-sky-700">

                                📷 nota.jpg

                            </div>

                            <div class="w-fit rounded-2xl bg-white/10 px-4 py-2">

                                OCR berhasil

                                <br>

                                Pengeluaran Rp125.000

                            </div>

                        </div>

                    </div>

                </div>

                <div class="text-sm text-sky-100/70">

                    © {{ date('Y') }} FinanceBot

                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="flex items-center justify-center bg-slate-50 px-4 py-8 sm:px-6 sm:py-12">

                <div class="w-full max-w-md">

                    <!-- MOBILE HEADER -->
                    <div class="mb-8 text-center lg:hidden">

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white font-bold">

                            F

                        </div>

                        <h1 class="mt-4 text-2xl font-bold">

                            FinanceBot

                        </h1>

                        <p class="mt-2 text-sm text-slate-500">

                            Telegram Finance Assistant

                        </p>

                    </div>

                    <!-- CARD -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">

                        <div class="mb-8">

                            <div
                                class="mb-3 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-600">

                                🔒 Akses khusus pelanggan aktif

                            </div>

                            <h2 class="text-2xl font-bold text-slate-900">

                                Selamat Datang

                            </h2>

                            <p class="mt-2 text-sm text-slate-500">

                                Login untuk mengakses dashboard keuangan Anda.

                            </p>

                        </div>

                        <form id="loginForm" method="POST" action="{{ route('login') }}">
                            @csrf

                            <div>

                                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">

                                    Email

                                </label>

                                <input type="email" name="email" id="email" placeholder="nama@email.com"
                                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100">

                                <x-input-error :messages="$errors->get('email')" class="mt-2" />

                            </div>

                            <div class="mt-5">

                                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">

                                    Password

                                </label>

                                <input type="password" name="password" id="password" placeholder="••••••••"
                                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100">

                                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                            </div>

                            <button id="btnLogin" type="submit"
                                class="mt-6 w-full rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 py-3.5 font-semibold text-white shadow-lg shadow-sky-200 transition hover:scale-[1.01]">

                                Masuk

                            </button>

                        </form>

                        <div class="mt-6 border-t border-slate-100 pt-6">

                            <p class="text-center text-sm text-slate-500">

                                Belum memiliki akses FinanceBot?

                            </p>

                            <a href="https://lynk.id/densonn" target="_blank"
                                class="mt-4 flex w-full items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-700">

                                Berlangganan Melalui Lynk.id

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection

@push('addon-script')
    <script>
        document
            .getElementById('loginForm')
            .addEventListener('submit', function() {

                Swal.fire({
                    title: 'Memproses Login',
                    text: 'Mohon tunggu...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,

                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

            });
    </script>
@endpush
