<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HealthController;

// Health check routes (no authentication required)
Route::get('/health', [HealthController::class, 'health']);
Route::get('/ping', [HealthController::class, 'ping']);

require __DIR__ .'/api/location.php';
