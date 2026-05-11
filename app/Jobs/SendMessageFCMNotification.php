<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FCMService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SendMessageFCMNotification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(
        private readonly int $userId,
        private readonly int $threadId,
        private readonly string $senderName,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Unique key per user per thread to debounce notifications.
     */
    public function uniqueId(): string
    {
        return "msg_fcm_{$this->userId}_{$this->threadId}";
    }

    /**
     * Hold the unique lock for 60 seconds (delay 30s + processing buffer).
     */
    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(FCMService $fcmService): void
    {
        $user = User::find($this->userId);

        $cacheKey = "pending_msg_count:{$this->userId}:{$this->threadId}";
        $count = (int) Cache::pull($cacheKey, 1);

        if (! $user || empty($user->fcm_token)) {
            return;
        }

        $body = $count > 1
            ? __('notification.new_message.body_count', ['count' => $count])
            : __('notification.new_message.body');

        $fcmService->sendToUser($user, $this->senderName, $body, [
            'code' => 'NEW_MESSAGE',
            'thread_id' => (string) $this->threadId,
        ]);
    }
}
