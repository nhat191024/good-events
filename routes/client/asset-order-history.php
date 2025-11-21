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

    Route::get(
        '/{bill}/download',
        [AssetOrderController::class, 'download']
    )->whereNumber('bill')->name('download');

    Route::get(
        '/{bill}/download/all',
        [AssetOrderController::class, 'downloadAll']
    )->whereNumber('bill')->name('downloadAll');

    Route::get(
        '/{bill}/download/zip',
        [AssetOrderController::class, 'downloadZip']
    )->whereNumber('bill')->name('downloadZip');
});
