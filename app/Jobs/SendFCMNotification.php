<?php

namespace App\Jobs;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFCMNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param array<non-empty-string, string> $data
     */
    public function __construct(
        private readonly string $target,
        private readonly string $targetType,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
    ) {
        $this->onQueue('notifications');
    }

    public function handle(Messaging $messaging): void
    {
        $message = CloudMessage::fromArray([
            $this->targetType => $this->target,
            'notification' => Notification::create($this->title, $this->body),
            'data' => $this->data,
        ]);

        try {
            $messaging->send($message);
        } catch (MessagingException $e) {
            $this->fail($e);
        }
    }
}
