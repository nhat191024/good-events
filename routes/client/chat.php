<?php

use App\Http\Controllers\Client\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'verified.any', 'throttle:api')->group(function () {
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
