<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register/partner', [AuthController::class, 'registerPartner']);
Route::post('/login', [AuthController::class, 'login']);
