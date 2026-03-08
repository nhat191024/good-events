<?php

use App\Http\Controllers\Api\Client\BlogController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/blog/home', [BlogController::class, 'home']);
});
