<?php

use App\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/event/home', [HomeController::class, 'eventHome']);
    Route::get('/event/home/categories', [HomeController::class, 'loadMoreCategories']);
    Route::get('/event/home/children', [HomeController::class, 'loadMoreChildren']);
    Route::get('/event/home/search', [HomeController::class, 'search']);
});
