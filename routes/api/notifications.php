<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/{id}/read', [NotificationController::class, 'read']);
    Route::post('/read-all', [NotificationController::class, 'readAll']);
    Route::post('/{id}/delete', [NotificationController::class, 'destroy']);
});
