<?php
use App\Http\Controllers\Api\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/locations/{location}/wards', [LocationController::class, 'wards']);
