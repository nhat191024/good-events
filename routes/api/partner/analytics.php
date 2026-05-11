<?php

use App\Http\Controllers\Api\Partner\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::prefix('analytics')->group(function () {
    Route::get('/statistics', [AnalyticsController::class, 'statistics']);
    Route::get('/revenue-chart', [AnalyticsController::class, 'revenueChart']);
    Route::get('/top-services', [AnalyticsController::class, 'topServices']);
});
