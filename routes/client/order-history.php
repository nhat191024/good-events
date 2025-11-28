<?php

use App\Http\Controllers\Client\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::prefix('don-hang-cua-toi')->name('client-orders.')->group(function () {
        Route::get(
            '/',
            [OrderController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/{bill}/details',
            [OrderController::class, 'getDetails']
        )->name('details');

        Route::get(
            '/partner-profile/{user}.json',
            [OrderController::class, 'getPartnerProfile']
        )
            ->whereNumber('user')
            ->name('partner-profile');

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

        Route::post(
            '/validate-voucher',
            [OrderController::class, 'validateVoucher']
        )->name('validate-voucher');

        Route::post(
            '/get-voucher-discount-amount',
            [OrderController::class, 'getVoucherDiscountAmount']
        )->name('get-voucher-discount-amount');

        require __DIR__.'/asset-order-history.php';
    });
});
