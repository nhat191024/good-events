<?php

use App\Http\Controllers\Api\ClientToPartnerController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:api');
Route::post('/login/google', [LoginController::class, 'loginGoogle'])->middleware('throttle:api');
Route::post('/forgot', [LoginController::class, 'forgot'])->middleware('throttle:api');

Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:api');
Route::post('/register/partner', [RegisterController::class, 'registerPartner'])->middleware('throttle:api');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/check-token', [LoginController::class, 'checkToken']);
    Route::get('/register/partner/from-client', [ClientToPartnerController::class, 'form']);
    Route::post('/register/partner/from-client', [ClientToPartnerController::class, 'store']);
});
