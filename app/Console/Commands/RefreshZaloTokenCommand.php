<?php

namespace App\Console\Commands;

use App\Services\ZaloService;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshZaloTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-zalo-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Zalo access token using refresh token';

    /**
     * Execute the console command.
     */
    public function handle(ZaloService $zaloService)
    {
        $this->info('Starting to refresh Zalo access token...');

        try {
            $response = $zaloService->getNewAccessToken();

            if (isset($response['access_token'])) {
                $this->info('Successfully refreshed Zalo access token.');
                Log::info('Zalo access token refreshed successfully.');
            } else {
                $this->error('Failed to refresh Zalo access token.');
                Log::error('Failed to refresh Zalo access token', ['response' => $response]);
            }
        } catch (\Exception $e) {
            $this->error('Error refreshing Zalo access token: ' . $e->getMessage());
            Log::error('Exception while refreshing Zalo access token: ' . $e->getMessage());
        }
    }
}
