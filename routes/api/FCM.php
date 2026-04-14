<?php

use App\Http\Controllers\Api\Common\FCMController;
use Illuminate\Support\Facades\Route;

Route::post('/fcm/update-token', [FCMController::class, 'updateToken']);
