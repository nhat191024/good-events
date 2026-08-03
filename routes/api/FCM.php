<?php

use App\Http\Controllers\Api\Common\FCMController;
use App\Http\Controllers\Api\Common\PushDeviceController;
use Illuminate\Support\Facades\Route;

Route::post('/fcm/update-token', [FCMController::class, 'updateToken']);
Route::post('/push/devices', [PushDeviceController::class, 'store']);
Route::delete('/push/devices/{deviceId}', [PushDeviceController::class, 'destroy']);
