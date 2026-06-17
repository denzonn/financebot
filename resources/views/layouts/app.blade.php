<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    @include('includes.style')

    <link rel="icon" href="{{ asset('images/Logo.png') }}">
</head>

<body class="bg-slate-50 font-poppins text-slate-800">

    <div class="min-h-screen">

        {{-- DESKTOP SIDEBAR --}}
        <aside class="fixed left-0 top-0 hidden h-screen w-72 border-r border-slate-200 bg-white lg:flex lg:flex-col">

            @include('includes.sidebar')

        </aside>

        {{-- MOBILE HEADER --}}
        <header class="sticky top-0 z-50 border-b border-slate-200 bg-white px-4 py-3 lg:hidden">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <img src="{{ asset('images/Logo.png') }}" class="h-10 w-10">

                    <div>

                        <h1 class="font-bold">
                            FinanceBot
                        </h1>

                        <p class="text-xs text-slate-500">
                            Smart Finance Tracking
                        </p>

                    </div>

                </div>

            </div>

        </header>

        {{-- CONTENT --}}
        <main class="lg:ml-72">

            {{-- DESKTOP TOPBAR --}}
            <div class="hidden h-20 items-center justify-between border-b border-slate-200 bg-white px-8 lg:flex">

                <div>

                    <h1 class="text-2xl font-bold text-slate-900">
                        @yield('page_title')
                    </h1>

                    <p class="text-sm text-slate-500">
                        Selamat datang kembali 👋
                    </p>

                </div>

                <div class="flex items-center gap-4">

                    <div class="rounded-2xl border border-slate-200 px-4 py-2">

                        <p class="text-xs text-slate-500">
                            Saldo Saat Ini
                        </p>

                        <p class="font-semibold">
                            Rp 0
                        </p>

                    </div>

                    @include('includes.admin.navbar')

                </div>

            </div>

            <div class="p-4 pb-28 lg:p-8">

                @yield('content')

            </div>

        </main>

        {{-- MOBILE FLOATING NAV --}}
        <nav class="fixed bottom-5 left-1/2 z-50 w-[92%] max-w-md -translate-x-1/2 lg:hidden">

            <div class="rounded-3xl border border-slate-200/80 bg-white/95 px-2 py-2 shadow-2xl backdrop-blur-xl">

                <div class="grid grid-cols-5 items-center">

                    {{-- TRANSAKSI --}}
                    <a href="#" class="flex flex-col items-center gap-1 py-2 text-slate-500">

                        <i class="fa-solid fa-receipt text-lg"></i>

                        <span class="text-[11px]">
                            Transaksi
                        </span>

                    </a>

                    {{-- TELEGRAM --}}
                    <a href="{{ route('user.bot') }}" class="flex flex-col items-center gap-1 py-2 text-slate-500">

                        <i class="fa-brands fa-telegram text-lg"></i>

                        <span class="text-[11px]">
                            Bot
                        </span>

                    </a>

                    {{-- DASHBOARD --}}
                    <a href="{{ route('user.dashboard') }}"
                        class="relative -mt-10 flex h-16 w-16 items-center justify-center justify-self-center rounded-full bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-xl">

                        <i class="fa-solid fa-house text-xl"></i>

                    </a>

                    {{-- LAPORAN --}}
                    <a href="#" class="flex flex-col items-center gap-1 py-2 text-slate-500">

                        <i class="fa-solid fa-chart-pie text-lg"></i>

                        <span class="text-[11px]">
                            Laporan
                        </span>

                    </a>

                    {{-- AKUN --}}
                    <a href="#" class="flex flex-col items-center gap-1 py-2 text-slate-500">

                        <i class="fa-solid fa-user text-lg"></i>

                        <span class="text-[11px]">
                            Akun
                        </span>

                    </a>

                </div>

            </div>

        </nav>

    </div>

    <div id="mobileSidebar"
        class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full bg-white transition-transform duration-300 lg:hidden">

        @include('includes.sidebar')

    </div>

    <div id="mobileOverlay" class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden">
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @include('includes.script')
    <script>
        const dropdownBtn =
            document.getElementById('userDropdownBtn');

        const dropdown =
            document.getElementById('userDropdown');

        dropdownBtn?.addEventListener('click', function() {

            dropdown.classList.toggle('hidden');

        });

        document.addEventListener('click', function(e) {

            if (
                !document
                .getElementById('userDropdownWrapper')
                .contains(e.target)
            ) {

                dropdown.classList.add('hidden');

            }

        });
    </script>
    @stack('addon-script')

</body>

</html>
