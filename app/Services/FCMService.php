<?php

namespace App\Services;

use App\Jobs\SendFCMNotification;
use App\Models\User;

class FCMService
{
    /**
     * Dispatch a push notification job for a user via their FCM token.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], string $priority = '5'): bool
    {
        if (empty($user->fcm_token)) {
            return false;
        }

        return $this->sendToToken($user->fcm_token, $title, $body, $data, $priority);
    }

    /**
     * Dispatch a push notification job to a device token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], string $priority = '5'): bool
    {
        SendFCMNotification::dispatch($token, 'token', $title, $body, $data, $priority);

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
