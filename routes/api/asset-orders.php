<?php

use App\Http\Controllers\Api\AssetOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/asset-orders', [AssetOrderController::class, 'index']);
    Route::get('/asset-orders/{bill}', [AssetOrderController::class, 'show'])->whereNumber('bill');
    Route::post('/asset-orders/{bill}/repay', [AssetOrderController::class, 'repay'])->whereNumber('bill');
    Route::get('/asset-orders/{bill}/download', [AssetOrderController::class, 'download'])
        ->whereNumber('bill')
        ->name('api.asset-orders.download');
});
