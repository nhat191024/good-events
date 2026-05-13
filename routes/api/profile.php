<?php

use App\Http\Controllers\Api\Common\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'api.verified'])->group(function () {
    Route::get('/profile/me', [ProfileController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::get('/profile/{user}', [ProfileController::class, 'show'])->whereNumber('user');
