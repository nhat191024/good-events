<?php

use App\Http\Controllers\Api\Partner\BillController;
use Illuminate\Support\Facades\Route;

Route::get('/bills/realtime', [BillController::class, 'realtime']);
Route::post('/bills/{bill}/accept', [BillController::class, 'accept'])->whereNumber('bill');

Route::get('/bills/pending', [BillController::class, 'pending']);
Route::get('/bills/confirmed', [BillController::class, 'confirmed']);
Route::get('/bills/{bill}', [BillController::class, 'show'])->whereNumber('bill');
Route::post('/bills/{bill}/mark-in-job', [BillController::class, 'markInJob'])->whereNumber('bill');
Route::post('/bills/{bill}/complete', [BillController::class, 'complete'])->whereNumber('bill');
