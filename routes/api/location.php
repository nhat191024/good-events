<?php
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/locations/{location}/wards', [LocationController::class, 'wards'])
    ->middleware('throttle:api');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/locations', [ResourceController::class, 'index'])
        ->defaults('resource', 'locations');
    Route::get('/locations/{id}', [ResourceController::class, 'show'])
        ->whereNumber('id')
        ->defaults('resource', 'locations');
    Route::post('/locations', [ResourceController::class, 'store'])
        ->defaults('resource', 'locations');
    Route::post('/locations/{id}', [ResourceController::class, 'update'])
        ->whereNumber('id')
        ->defaults('resource', 'locations');
    Route::post('/locations/{id}/delete', [ResourceController::class, 'destroy'])
        ->whereNumber('id')
        ->defaults('resource', 'locations');
});
