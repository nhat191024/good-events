<?php
use App\Http\Controllers\Api\Client\LocationController;
use App\Http\Controllers\Api\Client\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/locations/{location}/wards', [LocationController::class, 'wards'])
    ->middleware('throttle:api');
Route::get('/locations', [LocationController::class, 'provinces'])
    ->middleware('throttle:api');
