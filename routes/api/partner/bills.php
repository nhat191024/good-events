<?php

use App\Http\Controllers\Api\Partner\BillController;
use Illuminate\Support\Facades\Route;

Route::get('/bills/realtime', [BillController::class, 'realtime']);
Route::get('/bills/history', [BillController::class, 'history']);
Route::post('/bills/{bill}/accept', [BillController::class, 'accept'])->whereNumber('bill');

Route::get('/bills/{status}', [BillController::class, 'list'])->whereIn('status', ['pending', 'confirmed']);
Route::get('/bills/{bill}', [BillController::class, 'show'])->whereNumber('bill');
Route::post('/bills/{bill}/mark-in-job', [BillController::class, 'markInJob'])->whereNumber('bill');
Route::post('/bills/{bill}/complete', [BillController::class, 'complete'])->whereNumber('bill');
