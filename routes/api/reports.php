<?php

use App\Http\Controllers\Api\Client\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/report/user', [ReportController::class, 'reportUser']);
Route::post('/report/bill', [ReportController::class, 'reportBill']);
