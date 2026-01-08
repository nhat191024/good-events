<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/orders', [OrderController::class, 'list']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::get('/orders/partner-profile/{user}', [OrderController::class, 'partnerProfile'])->whereNumber('user');
    Route::get('/orders/{order}', [OrderController::class, 'single'])->whereNumber('order');
    Route::get('/orders/{order}/details', [OrderController::class, 'details'])->whereNumber('order');
    Route::post('/orders/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/orders/choose-partner', [OrderController::class, 'confirmChoosePartner']);
    Route::post('/orders/submit-review', [OrderController::class, 'submitReview']);
    Route::post('/orders/validate-voucher', [OrderController::class, 'validateVoucher']);
    Route::post('/orders/voucher-discount', [OrderController::class, 'getVoucherDiscountAmount']);
});
