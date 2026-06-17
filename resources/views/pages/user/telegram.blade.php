@extends('layouts.app')

@section('title', 'Telegram Bot')
@section('page_title', 'Telegram Bot')

@section('content')

    <div class="space-y-6">

        @if ($telegram->telegram_id)
            {{-- CONNECTED --}}
            <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-500 to-green-600 p-6 text-white shadow-xl">

                <div class="flex items-center justify-between">

                    <div>

                        <div class="mb-3 inline-flex rounded-full bg-white/20 px-3 py-1 text-xs">

                            🟢 Connected

                        </div>

                        <h2 class="text-2xl font-bold">

                            Telegram Berhasil Terhubung

                        </h2>

                        <p class="mt-2 text-emerald-100">

                            Semua transaksi dari Telegram akan otomatis masuk ke dashboard.

                        </p>

                    </div>

                    <i class="fa-solid fa-circle-check text-5xl"></i>

                </div>

            </div>

            <div class="rounded-3xl bg-white p-6 shadow-sm">

                <h3 class="mb-5 font-semibold text-slate-900">

                    Informasi Telegram

                </h3>

                <div class="space-y-5">

                    <div>

                        <p class="text-sm text-slate-500">

                            Nama Telegram

                        </p>

                        <h4 class="font-semibold">

                            {{ $telegram->telegram_name }}

                        </h4>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">

                            Username

                        </p>

                        <h4 class="font-semibold">

                            {{ '@' . $telegram->telegram_username }}

                        </h4>

                    </div>

                    <div>

                        <p class="text-sm text-slate-500">

                            Connected At

                        </p>

                        <h4 class="font-semibold">

                            {{ optional($telegram->connected_at)->format('d M Y H:i') }}

                        </h4>

                    </div>

                </div>

            </div>

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5">

                <div class="flex gap-3">

                    <i class="fa-brands fa-telegram text-xl text-emerald-600"></i>

                    <div>

                        <h4 class="font-semibold text-emerald-700">

                            Telegram Aktif

                        </h4>

                        <p class="mt-1 text-sm text-emerald-600">

                            Anda dapat langsung mengirim transaksi ke bot Telegram.

                        </p>

                    </div>

                </div>

            </div>
        @else
            {{-- HERO --}}
            <div
                class="overflow-hidden rounded-[28px] bg-gradient-to-br from-sky-600 via-sky-500 to-indigo-600 p-5 text-white shadow-xl">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <div class="mb-3 inline-flex rounded-full bg-white/20 px-3 py-1 text-xs">

                            🔗 Telegram Integration

                        </div>

                        <h2 class="text-xl font-bold sm:text-2xl">

                            Hubungkan Telegram Bot

                        </h2>

                        <p class="mt-2 text-sm text-sky-100">

                            Hubungkan Telegram Anda untuk mulai mencatat transaksi secara otomatis.

                        </p>

                    </div>

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15">

                        <i class="fa-brands fa-telegram text-3xl"></i>

                    </div>

                </div>

            </div>

            {{-- STEP 1 --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">

                        1

                    </div>

                    <div>

                        <h3 class="font-semibold">

                            Buka Bot Telegram

                        </h3>

                        <p class="text-sm text-slate-500">

                            Klik tombol berikut untuk membuka bot.

                        </p>

                    </div>

                </div>

                <a href="https://t.me/sonn_finance_bot" target="_blank"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-sky-600 px-5 py-3 text-white">

                    <i class="fa-brands fa-telegram mr-2"></i>

                    Buka Telegram Bot

                </a>

            </div>

            {{-- STEP 2 --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">

                        2

                    </div>

                    <div>

                        <h3 class="font-semibold">

                            Kirim Command Berikut

                        </h3>

                        <p class="text-sm text-slate-500">

                            Salin command lalu kirim ke Telegram Bot.

                        </p>

                    </div>

                </div>

                <div class="mt-5 rounded-3xl border border-dashed border-sky-200 bg-sky-50 p-5">

                    <h2 id="connectCommand" class="break-all text-center font-mono text-lg font-bold text-sky-700">

                        /connect {{ $telegram->connect_code }}

                    </h2>

                </div>

                <button onclick="copyCommand()" class="mt-4 w-full rounded-2xl bg-sky-600 py-3 font-medium text-white">

                    <i class="fa-regular fa-copy mr-2"></i>

                    Copy Command

                </button>

            </div>
        @endif

    </div>

    <div id="copyToast"
        class="pointer-events-none fixed left-1/2 top-6 z-[9999] -translate-x-1/2 translate-y-[-30px] opacity-0 transition-all duration-300">

        <div class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-medium text-white shadow-2xl">

            <span id="copyToastText">
                Berhasil disalin
            </span>

        </div>

    </div>

@endsection

@push('addon-script')
    <script>
        function showToast(message) {
            const toast =
                document.getElementById('copyToast');

            document.getElementById(
                'copyToastText'
            ).innerText = message;

            toast.classList.remove(
                'opacity-0',
                '-translate-y-8'
            );

            toast.classList.add(
                'opacity-100',
                'translate-y-0'
            );

            setTimeout(() => {

                toast.classList.remove(
                    'opacity-100',
                    'translate-y-0'
                );

                toast.classList.add(
                    'opacity-0',
                    '-translate-y-8'
                );

            }, 2000);
        }

        function copyCommand() {
            const command =
                document.getElementById(
                    'connectCommand'
                ).innerText.trim();

            navigator.clipboard.writeText(
                command
            );

            showToast(
                'Command berhasil disalin'
            );
        }
    </script>
@endpush
