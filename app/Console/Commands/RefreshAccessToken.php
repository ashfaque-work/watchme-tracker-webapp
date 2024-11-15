<?php

namespace App\Console\Commands;

use App\Models\AccessToken;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-access-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh access token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $accessToken = $this->token();
            $expiration = now()->addHours(1);

            AccessToken::updateOrCreate(
                ['id' => 1],
                ['access_token' => $accessToken, 'expires_at' => $expiration]
            );
            
            Log::info('Google API Access Token Refreshed at :'. now());
            $this->info('Access token refreshed successfully.');
        } catch (Exception $e) {
            Log::error('Error refreshing access token: ' . $e->getMessage());
            $this->error('Error refreshing access token.');
        }
    }

    private function token()
    {
        $client_id = \Config('services.google.client_id');
        $client_secret = \Config('services.google.client_secret');
        $refresh_token = \Config('services.google.refresh_token');
        Log::info('Google API Access Token Refreshed at :'. $refresh_token);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token'
            ]
        ]);

        $access_token = json_decode($response->getBody(), true)['access_token'];
        return $access_token;
    }
}
