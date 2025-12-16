<?php

use App\Http\Controllers\Client\ChatController;
use App\Http\Controllers\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth', 'verified')->group(function () {
    Route::get(
        '/chat',
        [ChatController::class, 'index']
    )->name('chat.index');

    Route::get(
        '/chat/threads',
        [ChatController::class, 'loadThreads']
    )->name('chat.threads.load');

    Route::get(
        '/chat/threads/{thread}/messages',
        [ChatController::class, 'loadMessages']
    )->name('chat.messages.load');

    Route::post(
        '/chat/threads/{thread}/messages',
        [ChatController::class, 'sendMessage']
    )->name('chat.messages.send');
});
