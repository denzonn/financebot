@extends('layouts.app')

@section('title', 'Profile')
@section('page_title', 'Profile')

@section('content')

    <div class="space-y-6">

        {{-- PROFILE HEADER --}}
        <div
            class="overflow-hidden rounded-[32px] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 p-6 text-white shadow-xl">

            <div
                class="flex flex-col items-center text-center gap-5 md:flex-row md:items-center md:justify-between md:text-left">

                <div class="flex flex-col items-center gap-4 md:flex-row">

                    <div
                        class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border-4 border-white/20 bg-white/10 text-4xl font-black">

                        {{ strtoupper(substr($user->name, 0, 1)) }}

                    </div>

                    <div>

                        <h2 class="text-2xl font-bold md:text-3xl">

                            {{ $user->name }}

                        </h2>

                        <p class="mt-1 text-sky-100">

                            {{ $user->email }}

                        </p>

                        <div class="mt-4 flex flex-wrap justify-center md:justify-start items-center gap-2">

                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium backdrop-blur">

                                FinanceBot Member

                            </span>

                            @if ($user->telegramAccount?->telegram_id)
                                <span class="rounded-full bg-green-500/20 px-3 py-1 text-xs font-medium text-green-100">

                                    <i class="fa-solid fa-circle-check mr-1"></i>
                                    Telegram Connected

                                </span>
                            @else
                                <span class="rounded-full bg-red-500/20 px-3 py-1 text-xs font-medium text-red-100">

                                    <i class="fa-solid fa-circle-xmark mr-1"></i>
                                    Telegram Not Connected

                                </span>
                            @endif

                        </div>

                    </div>

                </div>

                {{-- QUICK INFO DESKTOP --}}
                <div class="hidden md:flex gap-3">

                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">

                        <p class="text-xs text-sky-100">

                            Bergabung

                        </p>

                        <h4 class="mt-1 font-semibold">

                            {{ $user->created_at->format('d M Y') }}

                        </h4>

                    </div>

                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">

                        <p class="text-xs text-sky-100">

                            Status

                        </p>

                        <h4 class="mt-1 font-semibold">

                            Active

                        </h4>

                    </div>

                </div>

            </div>

        </div>

        {{-- TELEGRAM --}}
        <div class="rounded-3xl bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <h3 class="font-semibold">

                        Telegram Bot

                    </h3>

                    <p class="text-sm text-slate-500">

                        Status koneksi Telegram

                    </p>

                </div>

                @if ($user->telegramAccount?->telegram_id)
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

        {{-- MENU --}}
        <div class="rounded-3xl bg-white shadow-sm overflow-hidden">

            <a href="{{ route('user.bot') }}"
                class="flex items-center justify-between border-b px-5 py-4 hover:bg-slate-50">

                <div class="flex items-center gap-3">

                    <i class="fa-brands fa-telegram text-sky-500"></i>

                    <span>
                        Telegram Bot
                    </span>

                </div>

                <i class="fa-solid fa-chevron-right text-slate-400"></i>

            </a>

            <button type="button" onclick="openPasswordModal()"
                class="flex w-full items-center justify-between border-b px-5 py-4 hover:bg-slate-50">

                <div class="flex items-center gap-3">

                    <i class="fa-solid fa-lock text-amber-500"></i>

                    <span>
                        Ubah Password
                    </span>

                </div>

                <i class="fa-solid fa-chevron-right text-slate-400"></i>

            </button>

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button class="flex w-full items-center justify-between px-5 py-4 text-red-500 hover:bg-red-50">

                    <div class="flex items-center gap-3">

                        <i class="fa-solid fa-right-from-bracket"></i>

                        <span>
                            Logout
                        </span>

                    </div>

                    <i class="fa-solid fa-chevron-right"></i>

                </button>

            </form>

        </div>

    </div>

    {{-- MODAL PASSWORD --}}
    <div id="passwordModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 invisible transition-all duration-300">

        {{-- BACKDROP --}}
        <div onclick="closePasswordModal()" class="absolute inset-0 bg-black/50 backdrop-blur-sm">
        </div>

        {{-- MODAL CONTENT --}}
        <div id="passwordModalContent"
            class="relative w-full max-w-md translate-y-6 scale-95 overflow-hidden rounded-3xl bg-white shadow-2xl transition-all duration-300">

            {{-- HEADER --}}
            <div class="bg-gradient-to-r from-sky-50 to-blue-50 border-b border-slate-100 px-6 py-5">

                <div class="flex items-center justify-between">

                    <div>

                        <h3 class="text-lg font-bold text-slate-800">

                            Ubah Password

                        </h3>

                        <p class="text-sm text-slate-500">

                            Pastikan password baru mudah diingat.

                        </p>

                    </div>

                    <button type="button" onclick="closePasswordModal()"
                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-white hover:text-slate-700">

                        <i class="fa-solid fa-xmark"></i>

                    </button>

                </div>

            </div>

            {{-- FORM --}}
            <form id="passwordForm" action="{{ route('user.profile.password.update') }}" method="POST">

                @csrf

                <div class="space-y-4 p-6">

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Password Saat Ini

                        </label>

                        <input type="password" name="current_password" required
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Password Baru

                        </label>

                        <input type="password" name="password" required id="password"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-4 focus:ring-sky-100">

                    </div>

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Konfirmasi Password Baru

                        </label>

                        <input type="password" name="password_confirmation" required id="password_confirmation"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3">

                        <p id="passwordMatchMessage" class="mt-2 hidden text-xs">
                        </p>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex gap-3 border-t border-slate-100 p-6">

                    <button type="button" onclick="closePasswordModal()"
                        class="flex-1 rounded-2xl bg-slate-100 py-3 font-medium text-slate-700 transition hover:bg-slate-200">

                        Batal

                    </button>

                    <button type="submit" id="btnSubmitPassword"
                        class="flex-1 rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 py-3 font-medium text-white shadow-lg shadow-sky-500/20 transition hover:scale-[1.02] hover:shadow-xl">

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>
    @if ($errors->any() || session('toast_error'))
        <script>
            window.addEventListener(
                'load',
                function() {

                    openPasswordModal();

                }
            );
        </script>
    @endif
@endsection

@push('addon-script')
    <script>
        function openPasswordModal() {

            const modal =
                document.getElementById(
                    'passwordModal'
                );

            const content =
                document.getElementById(
                    'passwordModalContent'
                );

            modal.classList.remove(
                'invisible',
                'opacity-0'
            );

            modal.classList.add(
                'opacity-100'
            );

            setTimeout(() => {

                content.classList.remove(
                    'translate-y-6',
                    'scale-95'
                );

                content.classList.add(
                    'translate-y-0',
                    'scale-100'
                );

            }, 10);
        }

        function closePasswordModal() {

            const modal =
                document.getElementById(
                    'passwordModal'
                );

            const content =
                document.getElementById(
                    'passwordModalContent'
                );

            content.classList.remove(
                'translate-y-0',
                'scale-100'
            );

            content.classList.add(
                'translate-y-6',
                'scale-95'
            );

            setTimeout(() => {

                modal.classList.remove(
                    'opacity-100'
                );

                modal.classList.add(
                    'opacity-0'
                );

                setTimeout(() => {

                    modal.classList.add(
                        'invisible'
                    );

                }, 300);

            }, 100);
        }

        document.addEventListener(
            'keydown',
            function(e) {

                if (e.key === 'Escape') {

                    closePasswordModal();

                }

            }
        );

        const passwordInput =
            document.getElementById(
                'password'
            );

        const confirmationInput =
            document.getElementById(
                'password_confirmation'
            );

        const passwordMatchMessage =
            document.getElementById(
                'passwordMatchMessage'
            );

        const submitButton =
            document.getElementById(
                'btnSubmitPassword'
            );

        function validatePasswordMatch() {

            const password =
                passwordInput.value;

            const confirmation =
                confirmationInput.value;

            if (confirmation === '') {

                passwordMatchMessage.classList.add(
                    'hidden'
                );

                submitButton.disabled =
                    false;

                submitButton.classList.remove(
                    'opacity-50',
                    'cursor-not-allowed'
                );

                return;
            }

            if (password === confirmation) {

                passwordMatchMessage.classList.remove(
                    'hidden'
                );

                passwordMatchMessage.className =
                    'mt-2 text-xs text-green-600';

                passwordMatchMessage.innerHTML =
                    '<i class="fa-solid fa-circle-check mr-1"></i> Password cocok';

                submitButton.disabled =
                    false;

                submitButton.classList.remove(
                    'opacity-50',
                    'cursor-not-allowed'
                );

            } else {

                passwordMatchMessage.classList.remove(
                    'hidden'
                );

                passwordMatchMessage.className =
                    'mt-2 text-xs text-red-600';

                passwordMatchMessage.innerHTML =
                    '<i class="fa-solid fa-circle-xmark mr-1"></i> Password tidak cocok';

                submitButton.disabled =
                    true;

                submitButton.classList.add(
                    'opacity-50',
                    'cursor-not-allowed'
                );
            }
        }

        passwordInput?.addEventListener(
            'input',
            validatePasswordMatch
        );

        confirmationInput?.addEventListener(
            'input',
            validatePasswordMatch
        );

        document
            .getElementById(
                'passwordForm'
            )
            ?.addEventListener(
                'submit',
                function(e) {

                    const password =
                        passwordInput.value;

                    const confirmation =
                        confirmationInput.value;

                    if (
                        password !==
                        confirmation
                    ) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Konfirmasi password tidak cocok.'
                        });

                        return false;
                    }

                    if (
                        password.length < 6
                    ) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Password minimal 6 karakter.'
                        });

                        return false;
                    }
                }
            );
    </script>
@endpush
