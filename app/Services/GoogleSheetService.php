<?php

namespace App\Services;

use App\Models\User;
use App\Models\GoogleSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    /**
     * Klien Google API yang sudah di-refresh token-nya kalau perlu.
     */
    private function getClient(): \Google\Client
    {
        $setting = GoogleSetting::find(1);

        if (!$setting || !$setting->refresh_token) {
            throw new \RuntimeException(
                'Google belum terhubung. Silakan connect dari dashboard.'
            );
        }

        $client = new \Google\Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

        $client->setAccessToken([
            'access_token'  => $setting->access_token,
            'refresh_token' => $setting->refresh_token,
        ]);

        if ($client->isAccessTokenExpired()) {

            $token = $client->fetchAccessTokenWithRefreshToken($setting->refresh_token);

            $setting->update([
                'access_token' => $token['access_token'],
                'expires_at'   => now()->addSeconds($token['expires_in']),
            ]);

            $client->setAccessToken($token);
        }

        return $client;
    }

    /**
     * Entry point: append transaksi baru ke sheet user.
     * Kalau user belum punya sheet, copy template dulu.
     */
    public function appendTransaction(Transaction $transaction)
    {
        $spreadsheetId = $this->getOrCreateUserSheet(
            $transaction->user_id
        );

        $this->appendRow($spreadsheetId, $transaction);
    }

    /**
     * Ambil spreadsheet_id untuk user. Kalau belum ada,
     * copy template (GOOGLE_SHEET_TEMPLATE_ID) ke folder
     * (GOOGLE_DRIVE_FOLDER_ID), simpan mapping, dan return id.
     */
    public function getOrCreateUserSheet(int $userId): string
    {
        $setting = GoogleSetting::find(1);
        $map     = $setting?->spreadsheet_map ?? [];

        if (isset($map[$userId]) && $map[$userId]) {
            return $map[$userId];
        }

        $newSpreadsheetId = $this->copyTemplate($userId);

        $map[$userId] = $newSpreadsheetId;

        if ($setting) {
            $setting->update(['spreadsheet_map' => $map]);
        }

        return $newSpreadsheetId;
    }

    /**
     * Copy template spreadsheet ke akun Google, simpan di folder
     * yang dikonfigurasi. Return id spreadsheet hasil copy.
     */
    private function copyTemplate(int $userId): string
    {
        $templateId = env('GOOGLE_SHEET_TEMPLATE_ID');

        if (!$templateId) {
            throw new \RuntimeException(
                'GOOGLE_SHEET_TEMPLATE_ID belum di-set di .env'
            );
        }

        $user   = User::findOrFail($userId);
        $client = $this->getClient();
        $drive  = new \Google\Service\Drive($client);

        $copyName = 'FinanceBot_' . $user->id . '_' . $user->name;

        $copiedFile = $drive->files->copy(
            $templateId,
            new \Google\Service\Drive\DriveFile(['name' => $copyName])
        );

        $spreadsheetId = $copiedFile->getId();

        /**
         * Pindahkan ke folder khusus kalau di-set.
         */
        if (env('GOOGLE_DRIVE_FOLDER_ID')) {
            try {

                $file = $drive->files->get($spreadsheetId, [
                    'fields' => 'parents',
                ]);

                $previousParents = implode(',', $file->parents ?: []);

                $drive->files->update(
                    $spreadsheetId,
                    new \Google\Service\Drive\DriveFile(),
                    [
                        'addParents'    => env('GOOGLE_DRIVE_FOLDER_ID'),
                        'removeParents' => $previousParents,
                    ]
                );
            } catch (\Exception $e) {

                Log::warning(
                    'Gagal pindahkan sheet ke folder: ' .
                        $e->getMessage()
                );
            }
        }

        return $spreadsheetId;
    }

    /**
     * Append baris transaksi ke sheet "Transaksi" di spreadsheet.
     * Asumsi template sudah punya header di baris 1 dan sheet
     * dengan nama "Transaksi".
     */
    private function appendRow(
        string $spreadsheetId,
        Transaction $transaction
    ) {
        $client  = $this->getClient();
        $service = new \Google\Service\Sheets($client);

        $values = [[
            $transaction->transaction_date->format('d/m/Y'),
            $transaction->transaction_date->format('H:i'),
            strtoupper($transaction->type),
            $transaction->category,
            $transaction->amount,
            $transaction->description,
            $transaction->transaction_date->format('Y-m'),
            $transaction->transaction_date->format('Y'),
        ]];

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values,
        ]);

        $service->spreadsheets_values->append(
            $spreadsheetId,
            "'Transaksi'!A:H",
            $body,
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );
    }
}
