<?php

use App\Http\Controllers\Api\Client\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('/blog/home', [BlogController::class, 'home']);
