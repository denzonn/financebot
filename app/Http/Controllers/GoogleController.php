<?php

namespace App\Http\Controllers;

use App\Models\GoogleSetting;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver(
            'google'
        )
            ->scopes([
                'https://www.googleapis.com/auth/drive',
                'https://www.googleapis.com/auth/spreadsheets'
            ])
            ->redirect();
    }

    public function callback()
    {
        $googleUser =
            Socialite::driver(
                'google'
            )
            ->stateless()
            ->user();

        GoogleSetting::updateOrCreate(
            ['id' => 1],
            [
                'access_token' =>
                $googleUser->token,

                'refresh_token' =>
                $googleUser->refreshToken,

                'expires_at' =>
                now()->addSeconds(
                    $googleUser->expiresIn
                ),
            ]
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Google Drive berhasil terhubung'
            );
    }
}
