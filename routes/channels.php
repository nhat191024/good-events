<?php

use Illuminate\Support\Facades\Broadcast;

// Only register channels if we have valid Pusher credentials
if (
    config('broadcasting.connections.pusher.key') &&
    config('broadcasting.connections.pusher.secret') &&
    config('broadcasting.connections.pusher.app_id')
) {

    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('category.{categoryId}', function ($user, $categoryId) {
        return $user->partnerServices()->where('id', $categoryId)->exists();
    });
}
