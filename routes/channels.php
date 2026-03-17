<?php

use Illuminate\Support\Facades\Broadcast;
use Cmgmyr\Messenger\Models\Participant;
use App\Enum\CacheKey;

// Only register channels if we have valid Pusher credentials
if (
    config('broadcasting.connections.pusher.key') &&
    config('broadcasting.connections.pusher.secret') &&
    config('broadcasting.connections.pusher.app_id')
) {

    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('App.Models.Partner.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('App.Models.Customer.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('category.{categoryId}', function ($user, $categoryId) {
        $key = CacheKey::USER_CATEGORY_EXISTS->value . "{$user->id}_{$categoryId}";
        return cache()->remember($key, now()->addMinutes(10), function () use ($user, $categoryId) {
            return $user->partnerServices()->where('category_id', $categoryId)->exists();
        });
    });

    Broadcast::channel('thread.{threadId}', function ($user, $threadId) {
        $key = CacheKey::THREAD_PARTICIPANT->value . "{$threadId}";
        $participantIds = cache()->remember($key, now()->addMinutes(10), function () use ($threadId) {
            return Participant::where('thread_id', $threadId)->pluck('user_id')->all();
        });

        return in_array($user->id, $participantIds);
    });

    Broadcast::channel('user-messages.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
    });
}
