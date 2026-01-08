<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index']);
    Route::get('/chat/threads', [ChatController::class, 'loadThreads']);
    Route::get('/chat/threads/{thread}/messages', [ChatController::class, 'loadMessages'])->whereNumber('thread');
    Route::post('/chat/threads/{thread}/messages', [ChatController::class, 'sendMessage'])->whereNumber('thread');
});
