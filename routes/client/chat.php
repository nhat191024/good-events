<?php

use App\Http\Controllers\Client\ChatController;
use App\Http\Controllers\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//! commented out for development only, users have to be logged in to access these routes
// Route::middleware('auth')->group(function () {
    Route::get(
        '/chat',
        [ChatController::class, 'index']
    )->name('chat.dashboard');
// });
