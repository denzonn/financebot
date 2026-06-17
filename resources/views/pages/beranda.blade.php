<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinanceBot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900">

    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur-xl">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <div class="flex h-14 sm:h-16 items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white font-bold text-sky-700">
                        <img src="{{ asset('images/Logo.png') }}" alt="FinanceBot Logo"
                            class="h-11 w-11 object-contain">
                    </div>
                    <div>
                        <div class="font-bold">FinanceBot</div>
                        <div class="text-xs text-slate-500">Telegram Finance Assistant</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/login" class="rounded-xl border px-3 py-2 text-xs sm:text-sm">Login</a>
                    <a href="https://lynk.id/densonn" target="_blank"
                        class="rounded-xl bg-sky-600 px-3 sm:px-4 py-2 text-xs sm:text-sm text-white">Berlangganan</a>
                </div>
            </div>
        </div>
    </header>

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-sky-50 via-white to-indigo-50"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 py-12 md:py-20">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <span
                        class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs sm:text-sm text-sky-700">🚀
                        Catat keuangan langsung dari Telegram</span>
                    <h1 class="mt-6 text-4xl leading-tight font-black sm:text-5xl lg:text-7xl">Catat Keuangan Langsung
                        Dari Telegram</h1>
                    <p class="mt-5 text-base sm:text-lg text-slate-600 leading-7">Kirim transaksi, upload nota, dan
                        lihat laporan otomatis
                        tanpa spreadsheet.</p>
                    <div class="mt-8 grid gap-3 sm:flex">
                        <a href="https://lynk.id/densonn" target="_blank"
                            class="w-full sm:w-auto rounded-2xl bg-sky-600 px-6 py-3.5 text-center font-semibold text-white">Berlangganan
                            Sekarang</a>
                        <a href="#fitur"
                            class="w-full sm:w-auto rounded-2xl border bg-white px-6 py-3.5 text-center font-semibold">Pelajari
                            Fitur</a>
                    </div>
                </div>
                <div class="rounded-3xl border bg-white p-4 sm:p-5 shadow-xl">
                    <div class="space-y-4">
                        <div class="ml-auto max-w-[80%] rounded-2xl bg-sky-600 px-4 py-3 text-white">+500000 jual logo
                        </div>
                        <div class="max-w-[85%] rounded-2xl bg-slate-100 px-4 py-3">✅ Transaksi berhasil disimpan</div>
                        <div class="ml-auto max-w-[80%] rounded-2xl bg-sky-600 px-4 py-3 text-white">📷 nota.jpg</div>
                        <div class="max-w-[85%] rounded-2xl bg-slate-100 px-4 py-3">OCR berhasil - Rp125.000</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 md:py-24" id="fitur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2 class="text-center text-3xl md:text-4xl font-bold">Semua Yang Anda Butuhkan</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-3xl border p-5 hover:border-sky-300 transition">Telegram Integration</div>
                <div class="rounded-3xl border p-5 hover:border-sky-300 transition">OCR Nota</div>
                <div class="rounded-3xl border p-5 hover:border-sky-300 transition">Dashboard Real-Time</div>
                <div class="rounded-3xl border p-5 hover:border-sky-300 transition">AI Parsing</div>
            </div>
        </div>
    </section>

    <section class="bg-gradient-to-r from-sky-600 to-indigo-600 py-16 md:py-24 text-white">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <h2 class="text-3xl md:text-5xl font-bold leading-tight">Mulai Kelola Keuangan Hari Ini</h2>
            <p class="mt-6">Akses penuh FinanceBot tersedia melalui Lynk.id.</p>
            <a href="https://lynk.id/densonn" target="_blank"
                class="mt-8 inline-flex w-full sm:w-auto justify-center rounded-2xl bg-white px-8 py-4 font-semibold text-slate-900">Berlangganan
                Sekarang</a>
        </div>
    </section>

    <footer class="bg-white border-t py-8 text-center text-slate-500">
        © {{ date('Y') }} FinanceBot
    </footer>

</body>

</html>
