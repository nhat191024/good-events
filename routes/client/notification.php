<?php

use App\Http\Controllers\Client\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'throttle:api'])
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'read'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('readAll');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
