<?php

use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//! commented out for development only, users have to be logged in to access these routes
Route::middleware('auth')->group(function () {
    Route::prefix("don-hang-cua-toi")->name('client-orders.')->group(function () {
        Route::get(
            '/',
            [OrderController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/{bill}/details',
            [OrderController::class, 'getDetails']
        )->name('details');

        Route::post(
            '/cancel-order',
            [OrderController::class, 'cancelOrder']
        )->name('cancel');

        Route::post(
            '/choose-partner',
            [OrderController::class, 'confirmChoosePartner']
        )->name('confirm-partner');

        Route::post(
            '/submit-review',
            [OrderController::class, 'submitReview']
        )->name('submit-review');
    });
});
