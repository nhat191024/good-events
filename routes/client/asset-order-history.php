<?php

use App\Http\Controllers\AssetOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('tai-lieu')->name('asset.')->group(function () {
    Route::get(
        '/',
        [AssetOrderController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/{bill}/details',
        [AssetOrderController::class, 'show']
    )->whereNumber('bill')->name('details');

    Route::post(
        '/{bill}/repay',
        [AssetOrderController::class, 'repay']
    )->whereNumber('bill')->name('repay');
});
