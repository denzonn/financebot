<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TelegramAccount;
use Illuminate\Support\Str;

class TelegramBotController extends Controller
{
    public function index()
    {
        $telegram = TelegramAccount::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        if (
            !$telegram->telegram_id &&
            !$telegram->connect_code
        ) {

            $telegram->update([
                'connect_code' => strtoupper(
                    'FB-' . Str::random(8)
                )
            ]);

            $telegram->refresh();
        }

        return view(
            'pages.user.telegram',
            compact('telegram')
        );
    }
}
