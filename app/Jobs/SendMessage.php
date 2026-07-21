<?php

namespace App\Jobs;

use App\Events\SendMessage as SendMessageEvent;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SendMessage implements ShouldBeUnique, ShouldQueue
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

    public function uniqueId(): string
    {
        return 'chat_message_'.$this->message['id'];
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    private function dispatchDebouncedFCMNotifications(): void
    {
        $threadId = $this->message['thread_id'];
        $senderName = $this->message['user']['name'] ?? 'Ai đó';

        foreach ($this->message['other_participant_ids'] ?? [] as $participantId) {
            $cacheKey = "pending_msg_count:{$participantId}:{$threadId}";
            Cache::add($cacheKey, 0, now()->addHour());
            Cache::increment($cacheKey);

            SendMessageFCMNotification::dispatch($participantId, $threadId, $senderName)
                ->delay(now()->addSeconds(30));
        }
    }
}
