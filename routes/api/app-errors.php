<?php

use App\Http\Controllers\Api\AppErrorReportController;
use Illuminate\Support\Facades\Route;

Route::post('/app-errors', [AppErrorReportController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.app-errors.store');
