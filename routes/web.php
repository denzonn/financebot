<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\User\LaporanController;
use App\Http\Controllers\User\TelegramBotController;
use App\Http\Controllers\User\TransaksiController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [BerandaController::class, 'index'])->name('home');

Route::prefix('admin')->middleware(['auth', 'role:webmaster'])->group(
    function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    }
);

Route::get(
    '/test-sheet',
    function () {

        return app(
            \App\Services\GoogleSheetService::class
        )->testCreate(2);
    }
);

Route::middleware(['auth'])
    ->group(function () {

        Route::get(
            '/google/connect',
            [GoogleController::class, 'redirect']
        );

        Route::get(
            '/google/callback',
            [GoogleController::class, 'callback']
        );
    });

Route::middleware(['auth', 'role:user'])->name('user.')->group(
    function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/telegram-bot', [TelegramBotController::class, 'index'])->name('bot');

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi');
        Route::delete(
            '/transaksi/{transaction}',
            [TransaksiController::class, 'destroy']
        )->name('transaksi.destroy');

        Route::get('/profile', [UserController::class, 'index'])->name('profile');
        Route::post('/profile/password-update', [UserController::class, 'updatePassword'])->name('profile.password.update');


        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    }
);

require __DIR__ . '/auth.php';
