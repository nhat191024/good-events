<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FCMService;
use Illuminate\Console\Command;

class SendTestFCMNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test
                            {user_id? : The ID of the user to send the notification to}
                            {--title= : Notification title}
                            {--body= : Notification body}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test FCM push notification to a user by their ID';

    public function __construct(private FCMService $fcmService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id') ?? $this->ask('Enter the user ID');

        /** @var User|null $user */
        $user = User::query()->find($userId);

        if ($user === null) {
            $this->error("User with ID [{$userId}] not found.");

            return self::FAILURE;
        }

        $deviceTokens = $user->pushDevices()
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->when(! empty($user->fcm_token), fn ($tokens) => $tokens->push($user->fcm_token))
            ->unique()
            ->values();

        if ($deviceTokens->isEmpty()) {
            $this->error("User [{$user->name}] (ID: {$user->id}) does not have a registered FCM device.");

            return self::FAILURE;
        }

        $title = $this->option('title') ?? $this->ask('Notification title', 'Test Notification');
        $body = $this->option('body') ?? $this->ask('Notification body', 'This is a test push notification.');

        $this->info("Sending FCM notification to user [{$user->name}] (ID: {$user->id})...");
        $this->line("  Devices   : {$deviceTokens->count()}");
        $this->line("  Title     : {$title}");
        $this->line("  Body      : {$body}");

        $success = $this->fcmService->sendToUser($user, $title, $body, ['CODE' => 'TEST_NOTIFICATION']);

        if ($success) {
            $this->info('Notification sent successfully.');

            return self::SUCCESS;
        }

        $this->error('Failed to send notification. Check Firebase credentials and registered push devices.');

        return self::FAILURE;
    }
}
