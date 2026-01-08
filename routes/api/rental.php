<?php

use App\Http\Controllers\Api\RentalController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/rental/home', [RentalController::class, 'home']);
    Route::get('/rental/search', [RentalController::class, 'search']);
    Route::get('/rental/detail/{categorySlug}/{rentProductSlug}', [RentalController::class, 'detail']);
});
