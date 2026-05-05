<?php

use Illuminate\Support\Facades\Route;
 
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\AuthApiController;

// Health check routes (no authentication required)
Route::get('/health', [HealthController::class, 'health'])->middleware('throttle:api');
Route::get('/ping', [HealthController::class, 'ping'])->middleware('throttle:api');

//* https://stackoverflow.com/a/31451123
$router->get('csrf-token', function() {
    return csrf_token();
});

require __DIR__ .'/api/location.php';

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthApiController::class, 'registerClient']);

    });