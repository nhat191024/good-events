<?php

use App\Http\Controllers\Api\Partner\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/partner/profile', [ProfileController::class, 'show']);
    Route::post('/partner/profile', [ProfileController::class, 'update']);
});
