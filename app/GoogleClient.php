<?php

namespace App;

use Illuminate\Support\Facades\Storage;

class GoogleClient
{
    const TOKEN_FILE = 'token.json';

    public static function getClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Timesheets');
        $client->addScope(\Google_Service_Drive::DRIVE);
        $client->addScope(\Google_Service_Sheets::SPREADSHEETS);
        $client->setClientId(env('CLI_ID'));
        $client->setClientSecret(env('CLI_SECRET'));
        $client->setAccessType('offline');

        if (Storage::disk('local')->exists(self::TOKEN_FILE)) {
            $accessToken = json_decode(Storage::disk('local')->get(self::TOKEN_FILE));
            $client->setAccessToken($accessToken);
        }

        $isAccessTokenExpired = $client->isAccessTokenExpired();

        if (!$isAccessTokenExpired) {
            $authUrl = $client->createAuthUrl();
        }

        if ($isAccessTokenExpired && $client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        }

        if ($isAccessTokenExpired) {
            Storage::disk('local')->put(self::TOKEN_FILE, $client->getAccessToken());
        }

        $client->getRefreshToken();
    }
}
