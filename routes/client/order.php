<?php

use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//! commented out for development only, users have to be logged in to access these routes
// Route::middleware('auth')->group(function () {
    Route::get(
        '/orders',
        [OrderController::class, 'index']
    )->name('orders.dashboard');
// });
