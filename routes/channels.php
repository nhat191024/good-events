<?php

use App\Enum\CacheKey;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;

// Only register channels if we have valid Pusher credentials
if (
    config('broadcasting.connections.pusher.key') &&
    config('broadcasting.connections.pusher.secret') &&
    config('broadcasting.connections.pusher.app_id')
) {
    $hasApprovedCategory = function ($user, int|string $categoryId): bool {
        $key = CacheKey::USER_CATEGORY_EXISTS->value . "{$user->id}_{$categoryId}";

        return Cache::remember($key, now()->addMinutes(10), function () use ($user, $categoryId) {
            return $user->partnerServices()
                ->where('category_id', $categoryId)
                ->where('status', 'approved')
                ->exists();
        });
    };

    $serviceAreaLocationIds = function ($user): array {
        return Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])
            ->rememberForever(CacheKey::PARTNER_SERVICE_AREAS->value . "_{$user->id}", function () use ($user): array {
                return $user->partnerServiceAreas()
                    ->pluck('location_id')
                    ->map(fn ($locationId): int => (int) $locationId)
                    ->unique()
                    ->values()
                    ->all();
            });
    };

    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('App.Models.Partner.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('App.Models.Customer.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('partner-orders.{partnerId}', function ($user, $partnerId) {
        return (int) $user->id === (int) $partnerId;
    });

    Broadcast::channel('category.{categoryId}', function ($user, $categoryId) use ($hasApprovedCategory, $serviceAreaLocationIds) {
        return $hasApprovedCategory($user, $categoryId)
            && empty($serviceAreaLocationIds($user));
    });

    Broadcast::channel('category.{categoryId}.location.{locationId}', function ($user, $categoryId, $locationId) use ($hasApprovedCategory, $serviceAreaLocationIds) {
        if (!$hasApprovedCategory($user, $categoryId)) {
            return false;
        }

        return in_array((int) $locationId, $serviceAreaLocationIds($user), true);
    });

    Broadcast::channel('thread.{threadId}', function ($user, $threadId) {
        $key = CacheKey::THREAD_PARTICIPANT->value . "{$threadId}";
        $participantIds = cache()->remember($key, now()->addWeek(), function () use ($threadId) {
            return Participant::where('thread_id', $threadId)->pluck('user_id')->all();
        });

        return in_array($user->id, $participantIds);
    });

    Broadcast::channel('user-messages.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
    });
}
