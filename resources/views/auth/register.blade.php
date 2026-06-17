@extends('layouts.auth')

@section('title')
    Registrasi - Wahana Bermain
@endsection

@section('content')
    <div
        class="relative flex items-center justify-center min-h-screen w-full
                bg-gradient-to-b from-primary via-third to-secondary text-white overflow-hidden">

        <!-- Ornamen Background (opsional) -->
        <div class="absolute inset-0 opacity-10 bg-[url('/images/pattern.svg')] bg-repeat bg-center"></div>

        <!-- Konten Utama -->
        <div class="relative z-10 flex flex-col items-center justify-center px-4">
            <!-- Logo dan Judul -->
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo.png') }}" class="mx-auto h-16 mb-3 drop-shadow-lg" alt="Mandar Run Logo">
                <h1 class="text-2xl font-bold tracking-wide">Registrasi Wahana <span class="text-red-500">Bermain</span>
                </h1>
            </div>

            <!-- Card Form -->
            <div class="bg-white shadow-2xl rounded-2xl p-8 sm:p-10 w-full max-w-md text-gray-800 bg-opacity-95">
                <h2 class="text-center text-lg font-semibold text-gray-900 mb-6">Silahkan Lengkapi Form Registrasi Berikut
                </h2>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium mb-1">Nama Lengkap</label>
                        <input type="text" placeholder="Masukkan Nama Lengkap" name="nama_lengkap" id="nama_lengkap"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary outline-none transition">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" placeholder="Masukkan Email" name="email" id="email"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary outline-none transition">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <label for="password" class="block text-sm font-medium mb-1">No. Whatsapp</label>
                        <input type="number" placeholder="Masukkan No Whatsapp" name="no_whatsapp" id="no_whatsapp"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary outline-none transition">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="w-full mt-6">
                        <button class="w-full bg-primary hover:bg-primary text-white py-2 px-4 rounded text-sm">
                            Registrasi
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <span class="text-sm text-gray-600">Sudah punya akun?</span>
                        <a href="{{ route('login') }}" class="text-sm text-primary font-semibold hover:underline ml-1">
                            Masuk di sini
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
