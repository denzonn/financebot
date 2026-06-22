<?php

namespace App\Services;

use App\Models\User;
use App\Models\GoogleSheet;
use App\Models\GoogleSetting;
use App\Models\Transaction;

class GoogleSheetService
{
    private function getClient()
    {
        $setting =
            GoogleSetting::findOrFail(1);

        $client =
            new \Google\Client();

        $client->setClientId(
            env('GOOGLE_CLIENT_ID')
        );

        $client->setClientSecret(
            env('GOOGLE_CLIENT_SECRET')
        );

        $client->setAccessToken([
            'access_token' =>
            $setting->access_token,

            'refresh_token' =>
            $setting->refresh_token,
        ]);

        if (
            $client->isAccessTokenExpired()
        ) {

            $token =
                $client
                ->fetchAccessTokenWithRefreshToken(
                    $setting
                        ->refresh_token
                );

            $setting->update([
                'access_token' =>
                $token['access_token'],

                'expires_at' =>
                now()->addSeconds(
                    $token['expires_in']
                )
            ]);
        }

        return $client;
    }

    public function appendTransaction(
        Transaction $transaction
    ) {

        $googleSheet =
            GoogleSheet::firstOrCreate(
                [
                    'user_id' =>
                    $transaction->user_id
                ],
                $this->createSpreadsheet(
                    $transaction->user_id
                )
            );

        $this->appendRow(
            $googleSheet,
            $transaction
        );
    }

    private function createSpreadsheet(
        int $userId
    ) {

        $user =
            User::findOrFail(
                $userId
            );

        $client =
            $this->getClient();

        $service =
            new \Google\Service\Sheets(
                $client
            );

        $spreadsheet =
            new \Google\Service\Sheets\Spreadsheet([
                'properties' => [
                    'title' =>
                    'FinanceBot_' .
                        $user->id .
                        '_' .
                        $user->name
                ]
            ]);

        $spreadsheet =
            $service
            ->spreadsheets
            ->create(
                $spreadsheet
            );

        $spreadsheetId =
            $spreadsheet
            ->getSpreadsheetId();

        return [
            'spreadsheet_id' =>
            $spreadsheetId,

            'spreadsheet_name' =>
            'FinanceBot_' .
                $user->id .
                '_' .
                $user->name,

            'sheet_name' =>
            'Sheet1'
        ];
    }

    private function appendRow(
        GoogleSheet $googleSheet,
        Transaction $transaction
    ) {

        $client =
            $this->getClient();

        $service =
            new \Google\Service\Sheets(
                $client
            );

        $values = [[

            $transaction
                ->transaction_date
                ->format('d/m/Y'),

            strtoupper(
                $transaction->type
            ),

            $transaction->category,

            $transaction->amount,

            $transaction->description

        ]];

        $body =
            new \Google\Service\Sheets\ValueRange([
                'values' => $values
            ]);

        $service
            ->spreadsheets_values
            ->append(
                $googleSheet
                    ->spreadsheet_id,
                'Sheet1!A:E',
                $body,
                [
                    'valueInputOption' =>
                    'USER_ENTERED'
                ]
            );
    }
}
