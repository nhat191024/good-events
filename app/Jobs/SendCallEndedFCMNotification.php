<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;

class SendCallEndedFCMNotification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /** @param array<non-empty-string, string> $data */
    public function __construct(
        private readonly string $token,
        private readonly string $callId,
        private readonly array $data,
    ) {
        $this->onQueue('notifications');
    }

    public function uniqueId(): string
    {
        return hash('sha256', "call-ended:{$this->token}:{$this->callId}");
    }

    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(Messaging $messaging): void
    {
        $message = CloudMessage::fromArray([
            'token' => $this->token,
            'data' => $this->data,
        ])->withAndroidConfig([
            'priority' => 'high',
            'ttl' => '60s',
            'collapse_key' => "call_{$this->callId}",
        ]);

        try {
            $messaging->send($message);
        } catch (MessagingException $exception) {
            $this->fail($exception);
        }
    }
}
