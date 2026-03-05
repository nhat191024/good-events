<?php

use App\Http\Controllers\Api\Client\ClientToPartnerController;
use App\Http\Controllers\Api\Client\LoginController;
use App\Http\Controllers\Api\Client\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:api');
Route::post('/login/google', [LoginController::class, 'loginGoogle'])->middleware('throttle:api');
Route::post('/forgot', [LoginController::class, 'forgot'])->middleware('throttle:api');

Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:api');
Route::post('/register/partner', [RegisterController::class, 'registerPartner'])->middleware('throttle:api');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::get('/check-token', [LoginController::class, 'checkToken']);
    Route::get('/register/partner/from-client', [ClientToPartnerController::class, 'form']);
    Route::post('/register/partner/from-client', [ClientToPartnerController::class, 'store']);
});
