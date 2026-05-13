<?php

use App\Http\Controllers\Api\Client\ClientToPartnerController;
use App\Http\Controllers\Api\Common\LoginController;
use App\Http\Controllers\Api\Common\RegisterController;
use App\Http\Controllers\Api\Common\VerifyController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/login/google', [LoginController::class, 'loginGoogle']);
Route::post('/auth/apple', [LoginController::class, 'loginApple']);
Route::match(['get', 'post'], '/auth/apple/callback', [LoginController::class, 'appleCallback']);
Route::post('/forgot', [LoginController::class, 'forgot']);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/register/partner', [RegisterController::class, 'registerPartner']);

Route::middleware(['auth:sanctum', 'api.verified'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout']);
    Route::get('/check-token', [LoginController::class, 'checkToken']);
    Route::get('/register/partner/from-client', [ClientToPartnerController::class, 'form']);
    Route::post('/register/partner/from-client', [ClientToPartnerController::class, 'store']);
    Route::delete('/account/delete', [LoginController::class, 'deleteAccount']);
    Route::post('/verify/send-otp', [VerifyController::class, 'sendOtp']);
    Route::post('/verify/otp', [VerifyController::class, 'verifyOtp']);
});
