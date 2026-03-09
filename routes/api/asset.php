<?php

use App\Http\Controllers\Api\Client\AssetController;
use Illuminate\Support\Facades\Route;

Route::get('/asset/home', [AssetController::class, 'home']);
Route::get('/asset/search', [AssetController::class, 'search']);
Route::get('/asset/detail/{categorySlug}/{fileProductSlug}', [AssetController::class, 'detail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/asset/purchase/{slug}', [AssetController::class, 'purchase']);
    Route::post('/asset/purchase', [AssetController::class, 'confirmPurchase']);
});
