<?php

use App\Http\Controllers\Api\Client\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile/me', [ProfileController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::get('/profile/{user}', [ProfileController::class, 'show'])->whereNumber('user');
