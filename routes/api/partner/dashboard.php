<?php

use App\Http\Controllers\Api\Partner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/partner/dashboard', [DashboardController::class, 'index']);
});
