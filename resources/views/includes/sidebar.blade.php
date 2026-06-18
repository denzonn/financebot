<div class="flex h-full flex-col bg-white">

    <div class="border-b border-slate-200 p-6">

        <div class="flex items-center gap-3">

            <img src="{{ asset('images/Logo.png') }}" class="h-12 w-12 object-contain">

            <div>

                <h2 class="font-bold text-lg">
                    FinanceBot
                </h2>

                <p class="text-xs text-slate-500">
                    Telegram Finance Assistant
                </p>

            </div>

        </div>

    </div>

    <div class="p-6">

        <div class="rounded-3xl bg-gradient-to-r from-sky-600 to-indigo-600 p-4 text-white">

            <p class="text-xs text-sky-100">
                Akun Aktif
            </p>

            <h3 class="mt-1 font-semibold">
                {{ auth()->user()->name }}
            </h3>

            <div class="mt-3 inline-flex rounded-full bg-white/20 px-3 py-1 text-xs">

                {{ ucfirst(auth()->user()->roles) }}

            </div>

        </div>

    </div>

    <nav class="flex-1 space-y-2 px-4">

        <a href="{{ route('user.dashboard') }}"
            class="flex items-center gap-3 rounded-2xl px-4 py-3
        {{ request()->routeIs('user.dashboard') ? 'bg-sky-600 text-white' : 'hover:bg-slate-100' }}">

            <i class="fa-solid fa-house"></i>

            Dashboard

        </a>

        <a href="{{ route('user.transaksi') }}"
            class="flex items-center gap-3 rounded-2xl px-4 py-3
        {{ request()->routeIs('user.transaksi') ? 'bg-sky-600 text-white' : 'hover:bg-slate-100' }}">

            <i class="fa-solid fa-receipt"></i>

            Transaksi

        </a>

        <a href="#"
            class="flex items-center gap-3 rounded-2xl px-4 py-3
        {{ request()->routeIs('#') ? 'bg-sky-600 text-white' : 'hover:bg-slate-100' }}">

            <i class="fa-solid fa-chart-pie"></i>

            Laporan

        </a>

        <a href="{{ route('user.bot') }}"
            class="flex items-center gap-3 rounded-2xl px-4 py-3
        {{ request()->routeIs('user.bot') ? 'bg-sky-600 text-white' : 'hover:bg-slate-100' }}">

            <i class="fa-brands fa-telegram"></i>

            Telegram Bot

        </a>

        <a href="#"
            class="flex items-center gap-3 rounded-2xl px-4 py-3
        {{ request()->routeIs('#') ? 'bg-sky-600 text-white' : 'hover:bg-slate-100' }}">

            <i class="fa-solid fa-user"></i>

            Akun

        </a>

    </nav>

    <div class="border-t border-slate-200 p-4">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button class="flex w-full items-center justify-center gap-2 rounded-2xl bg-red-50 px-4 py-3 text-red-600">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </button>

        </form>

    </div>

</div>
