<?php

use App\Http\Controllers\Api\Common\CallController;
use App\Http\Controllers\Api\Common\ChatController;
use App\Http\Controllers\Api\Common\ChatInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatController::class, 'index']);
Route::get('/chat/threads/{thread}/messages', [ChatController::class, 'loadMessages'])->whereNumber('thread');
Route::post('/chat/threads/{thread}/messages', [ChatController::class, 'sendMessage'])->whereNumber('thread');
Route::get('/chat/users/search', [ChatInvitationController::class, 'searchUsers']);
Route::post('/chat/threads/{thread}/invitations', [ChatInvitationController::class, 'invite'])->whereNumber('thread');
Route::post('/chat/threads/{thread}/invitations/accept', [ChatInvitationController::class, 'accept'])->whereNumber('thread');
Route::delete('/chat/threads/{thread}/participants/me', [ChatInvitationController::class, 'leave'])->whereNumber('thread');
Route::post('/chat/threads/{thread}/calls', [CallController::class, 'store'])
    ->whereNumber('thread')
    ->middleware('throttle:30,1');
Route::get('/chat/threads/{thread}/calls/active', [CallController::class, 'active'])
    ->whereNumber('thread');

Route::prefix('calls/{call}')->group(function () {
    Route::post('/join', [CallController::class, 'join'])->middleware('throttle:30,1');
    Route::post('/leave', [CallController::class, 'leave']);
    Route::post('/decline', [CallController::class, 'decline']);
    Route::post('/end', [CallController::class, 'end']);
});
