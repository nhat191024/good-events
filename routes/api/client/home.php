<?php

use App\Http\Controllers\Api\Client\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/event/home/categories', [HomeController::class, 'loadMoreCategories']);
Route::get('/event/home/children', [HomeController::class, 'loadMoreChildren']);
Route::get('/event/home/search', [HomeController::class, 'search']);

Route::middleware(['api.verified'])->group(function () {
    Route::get('/event/home', [HomeController::class, 'eventHome']);
});
