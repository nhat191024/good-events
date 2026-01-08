<?php

use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/report/user', [ReportController::class, 'reportUser']);
    Route::post('/report/bill', [ReportController::class, 'reportBill']);
});
