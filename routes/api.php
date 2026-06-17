<?php

use App\Http\Controllers\LynkController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|s
*/

Route::post('/telegram/webhook', TelegramController::class);
Route::post(
    '/telegram/webhook/bot',
    [TelegramWebhookController::class, 'handle']
);

Route::post('/webhook/lynk', [LynkController::class, 'handle']);
