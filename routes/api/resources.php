<?php

use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('resources')->group(function () {
    Route::get('/{resource}', [ResourceController::class, 'index']);
    Route::get('/{resource}/{id}', [ResourceController::class, 'show'])->whereNumber('id');
    Route::post('/{resource}', [ResourceController::class, 'store']);
    Route::post('/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id');
    Route::post('/{resource}/{id}/delete', [ResourceController::class, 'destroy'])->whereNumber('id');
});
