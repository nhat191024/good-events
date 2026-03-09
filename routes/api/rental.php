<?php

use App\Http\Controllers\Api\Client\RentalController;
use Illuminate\Support\Facades\Route;

Route::get('/rental/home', [RentalController::class, 'home']);
Route::get('/rental/search', [RentalController::class, 'search']);
Route::get('/rental/detail/{categorySlug}/{rentProductSlug}', [RentalController::class, 'detail']);
