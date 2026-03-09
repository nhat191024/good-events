<?php

use App\Http\Controllers\Api\Client\ResourceController;
use Illuminate\Support\Facades\Route;

Route::prefix('resources')->group(function () {
    Route::get('/{resource}', [ResourceController::class, 'index']);
    Route::get('/{resource}/{id}', [ResourceController::class, 'show'])->whereNumber('id');
    Route::post('/{resource}', [ResourceController::class, 'store']);
    Route::post('/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id');
    Route::post('/{resource}/{id}/delete', [ResourceController::class, 'destroy'])->whereNumber('id');
});
