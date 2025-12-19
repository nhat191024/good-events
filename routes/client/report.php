<?php

use App\Http\Controllers\Client\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/report/user', [ReportController::class, 'reportUser'])->name('report.user');
Route::post('/report/bill', [ReportController::class, 'reportBill'])->name('report.bill');
