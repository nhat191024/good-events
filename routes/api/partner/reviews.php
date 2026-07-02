<?php

use App\Http\Controllers\Api\Partner\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/reviews', [ReviewController::class, 'index']);
