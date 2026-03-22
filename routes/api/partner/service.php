<?php

use App\Http\Controllers\Api\Partner\PartnerServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/service/index', [PartnerServiceController::class, 'index']);
Route::post('/service', [PartnerServiceController::class, 'create']);
Route::get('/service/{serviceId}', [PartnerServiceController::class, 'show']);
Route::post('/service/{serviceId}', [PartnerServiceController::class, 'update']);
Route::post('/service/{serviceId}/images', [PartnerServiceController::class, 'uploadImages']);
Route::get('/service/{serviceId}/images', [PartnerServiceController::class, 'getImages']);
Route::delete('/service/{serviceId}/images/{mediaId}', [PartnerServiceController::class, 'deleteImage']);
