<?php

use App\Http\Controllers\AssetOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('tai-lieu')->name('asset.')->group(function () {
    Route::get(
        '/',
        [AssetOrderController::class, 'index']
    )->name('dashboard');

    // Route::get(
    //     '/{bill}/details',
    //     [AssetOrderController::class, 'getDetails']
    // )->name('details');

    // Route::post(
    //     '/cancel-order',
    //     [AssetOrderController::class, 'cancelOrder']
    // )->name('cancel');

    // Route::post(
    //     '/choose-partner',
    //     [AssetOrderController::class, 'confirmChoosePartner']
    // )->name('confirm-partner');

    // Route::post(
    //     '/submit-review',
    //     [AssetOrderController::class, 'submitReview']
    // )->name('submit-review');

    // Route::post(
    //     '/validate-voucher',
    //     [AssetOrderController::class, 'validateVoucher']
    // )->name('validate-voucher');

    // Route::post(
    //     '/get-voucher-discount-amount',
    //     [AssetOrderController::class, 'getVoucherDiscountAmount']
    // )->name('get-voucher-discount-amount');
});
