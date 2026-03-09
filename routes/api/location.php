<?php

use App\Http\Controllers\Api\Client\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/locations/{location}/wards', [LocationController::class, 'wards']);
Route::get('/locations', [LocationController::class, 'provinces']);
