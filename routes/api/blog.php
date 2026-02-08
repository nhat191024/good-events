<?php

use App\Http\Controllers\Api\BlogController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/blog/category', [BlogController::class, 'category']);
    Route::get('/blog/search', [BlogController::class, 'search']);
    Route::get('/blog/detail', [BlogController::class, 'detail']);
});
