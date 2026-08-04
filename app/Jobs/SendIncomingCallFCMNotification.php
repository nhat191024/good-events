<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;

class SendIncomingCallFCMNotification implements ShouldBeUnique, ShouldQueue
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
        return hash('sha256', "incoming-call:{$this->token}:{$this->callId}");
    }

    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(Messaging $messaging): void
    {
        $tokenFingerprint = substr(hash('sha256', $this->token), 0, 12);
        $message = CloudMessage::fromArray([
            'token' => $this->token,
            'data' => $this->data,
        ])->withAndroidConfig([
            'priority' => 'high',
            'ttl' => '60s',
            'collapse_key' => "call_{$this->callId}",
        ]);

        try {
            Log::info('[FCM][IncomingCall] Sending Android data message.', [
                'call_id' => $this->callId,
                'thread_id' => $this->data['thread_id'] ?? null,
                'type' => $this->data['type'] ?? null,
                'token_fingerprint' => $tokenFingerprint,
                'attempt' => $this->attempts(),
            ]);

            $messageId = $messaging->send($message);

            Log::info('[FCM][IncomingCall] Firebase accepted Android data message.', [
                'call_id' => $this->callId,
                'thread_id' => $this->data['thread_id'] ?? null,
                'token_fingerprint' => $tokenFingerprint,
                'firebase_message_id' => $messageId,
            ]);
        } catch (MessagingException $exception) {
            Log::error('[FCM][IncomingCall] Firebase rejected Android data message.', [
                'call_id' => $this->callId,
                'thread_id' => $this->data['thread_id'] ?? null,
                'token_fingerprint' => $tokenFingerprint,
                'attempt' => $this->attempts(),
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);
            $this->fail($exception);
        }
    }
}
