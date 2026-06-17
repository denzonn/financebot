<div class="relative" id="userDropdownWrapper">

    <button id="userDropdownBtn" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2">

        <div class="hidden text-right sm:block">

            <p class="text-sm font-semibold">
                {{ auth()->user()->name }}
            </p>

            <p class="text-xs text-slate-500">
                Premium User
            </p>

        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100">

            <i class="fa-regular fa-user text-sky-600"></i>

        </div>

    </button>

    <div id="userDropdown"
        class="absolute right-0 top-[110%] hidden w-72 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">

        <div class="border-b border-slate-100 p-5">

            <h3 class="font-semibold">
                {{ auth()->user()->name }}
            </h3>

            <p class="text-xs text-slate-500">
                {{ auth()->user()->email }}
            </p>

        </div>

        <div class="p-2">

            <a href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 hover:bg-slate-50">

                <i class="fa-solid fa-user"></i>

                Profil Saya

            </a>

            <a href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 hover:bg-slate-50">

                <i class="fa-brands fa-telegram"></i>

                Telegram Bot

            </a>

        </div>

        <div class="border-t border-slate-100 p-3">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-red-50 px-4 py-3 text-red-600">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </button>

            </form>

        </div>

    </div>

</div>
