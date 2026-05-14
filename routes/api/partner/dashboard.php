<?php

use App\Http\Controllers\Api\Partner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
