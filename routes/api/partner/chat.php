<?php

use App\Http\Controllers\Api\Common\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatController::class, 'index']);
Route::get('/chat/search', [ChatController::class, 'search']);
Route::get('/chat/threads/{thread}/messages', [ChatController::class, 'loadMessages'])->whereNumber('thread');
Route::post('/chat/threads/{thread}/messages', [ChatController::class, 'sendMessage'])->whereNumber('thread');
