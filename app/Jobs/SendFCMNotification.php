<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendFCMNotification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param  array<non-empty-string, string>  $data
     */
    public function __construct(
        private readonly string $target,
        private readonly string $targetType,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
        private readonly string $priority = '5',
    ) {
        $this->onQueue('notifications');
    }

    public function uniqueId(): string
    {
        return hash('sha256', serialize([
            $this->target,
            $this->targetType,
            $this->title,
            $this->body,
            $this->data,
        ]));
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(Messaging $messaging): void
    {
        $message = CloudMessage::fromArray([
            $this->targetType => $this->target,
            'notification' => Notification::create($this->title, $this->body),
            'data' => $this->data,
        ])
            ->withApnsConfig([
                'headers' => [
                    'apns-priority' => $this->priority,
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ])
            ->withAndroidConfig([
                'priority' => $this->priority === '10' ? 'high' : 'normal',
            ]);

        try {
            $messaging->send($message);
        } catch (MessagingException $e) {
            $this->fail($e);
        }
    }
}
