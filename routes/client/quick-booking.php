<?php

use App\Http\Controllers\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;

//! commented out for development only, users have to be logged in to access these routes
// Route::middleware('auth')->group(function () {
    Route::prefix("dat-show")->name('quick-booking.')->group(
        function () {
            Route::get(
                '/',
                [QuickBookingController::class, 'chooseCategory']
            )->name('choose-category');

            Route::get(
                '/{partner_category_slug}/chon-loai-doi-tac',
                [QuickBookingController::class, 'choosePartnerCategory']
            )->name('choose-partner-category');

            //? we split into 3 separated routes so every step
            //? can be linked from a detail page (ex: 'BOOK NOW!' button link/href)
            Route::get(
                '/{partner_category_slug}/{partner_child_category_slug}/dien-thong-tin',
                [QuickBookingController::class, 'fillOrderInfo']
            )->name('fill-info');

            Route::post(
                '/hoan-thanh',
                [QuickBookingController::class, 'saveBookingInfo']
            )->name('save-info');
        }
    );
// });
