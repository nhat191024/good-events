<?php

use App\Http\Controllers\Api\Partner\PartnerServiceAreaController;
use Illuminate\Support\Facades\Route;

Route::get('/service-areas', [PartnerServiceAreaController::class, 'index']);
Route::post('/service-areas', [PartnerServiceAreaController::class, 'store']);
Route::post('/service-areas/update', [PartnerServiceAreaController::class, 'update']);
