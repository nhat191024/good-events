<?php

namespace App\Services;

use App\Jobs\SendCallEndedFCMNotification;
use App\Jobs\SendFCMNotification;
use App\Jobs\SendIncomingCallFCMNotification;
use App\Models\User;

class FCMService
{
    /** Dispatch a push notification job to every registered device for a user. */
    public function sendToUser(User $user, string $title, string $body, array $data = [], string $priority = '5'): bool
    {
        $pushDevices = $user->relationLoaded('pushDevices')
            ? $user->pushDevices
            : $user->pushDevices()->whereNotNull('fcm_token')->get(['fcm_token']);

        $tokens = $pushDevices->pluck('fcm_token');

        if (! empty($user->fcm_token)) {
            $tokens->push($user->fcm_token);
        }

        $tokens = $tokens
            ->filter(fn (?string $token): bool => $token !== null && $token !== '')
            ->unique()
            ->values();

        foreach ($tokens as $token) {
            $this->sendToToken($token, $title, $body, $data, $priority);
        }

        return $tokens->isNotEmpty();
    }

    /**
     * Dispatch a push notification job to a device token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], string $priority = '5'): bool
    {
        SendFCMNotification::dispatch($token, 'token', $title, $body, $data, $priority);

        return true;
    }

    /** @param array<non-empty-string, string> $data */
    public function sendIncomingCallToAndroid(string $token, string $callId, array $data): bool
    {
        SendIncomingCallFCMNotification::dispatch($token, $callId, $data);

        return true;
    }

    /** @param array<non-empty-string, string> $data */
    public function sendCallEndedToAndroid(string $token, string $callId, array $data): bool
    {
        SendCallEndedFCMNotification::dispatch($token, $callId, $data);

        return true;
    }

    /**
     * Dispatch a push notification job to a topic.
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [], string $priority = '5'): bool
    {
        SendFCMNotification::dispatch($topic, 'topic', $title, $body, $data, $priority);

        return true;
    }
}
