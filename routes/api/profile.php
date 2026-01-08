<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/profile/me', [ProfileController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::middleware('throttle:api')->group(function () {
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->whereNumber('user');
});
