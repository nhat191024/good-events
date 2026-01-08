<?php

use App\Http\Controllers\Api\Partner\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/partner/chat', [ChatController::class, 'index']);
    Route::get('/partner/chat/search', [ChatController::class, 'search']);
    Route::get('/partner/chat/threads/{thread}/messages', [ChatController::class, 'loadMessages'])->whereNumber('thread');
    Route::post('/partner/chat/threads/{thread}/messages', [ChatController::class, 'sendMessage'])->whereNumber('thread');
});
