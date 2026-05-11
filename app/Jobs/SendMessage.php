<?php

namespace App\Jobs;

use App\Events\SendMessage as SendMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SendMessage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $message)
    {
        $this->onQueue('messages');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new SendMessageEvent($this->message));

        $this->dispatchDebouncedFCMNotifications();
    }

    private function dispatchDebouncedFCMNotifications(): void
    {
        $threadId = $this->message['thread_id'];
        $senderName = $this->message['user']['name'] ?? 'Ai đó';

        foreach ($this->message['other_participant_ids'] ?? [] as $participantId) {
            $cacheKey = "pending_msg_count:{$participantId}:{$threadId}";
            Cache::put($cacheKey, (int) Cache::get($cacheKey, 0) + 1, now()->addMinutes(5));

            SendMessageFCMNotification::dispatch($participantId, $threadId, $senderName)
                ->delay(now()->addSeconds(30));
        }
    }
}
