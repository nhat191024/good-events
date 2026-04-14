<?php

use App\Http\Controllers\Api\Common\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:search')->group(function () {
    Route::get('/locations/{location}/wards', [LocationController::class, 'wards']);
    Route::get('/locations', [LocationController::class, 'provinces']);
});
