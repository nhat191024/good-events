<?php

use App\Http\Controllers\Api\Partner\BillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/partner/bills/realtime', [BillController::class, 'realtime']);
    Route::post('/partner/bills/{bill}/accept', [BillController::class, 'accept'])->whereNumber('bill');

    Route::get('/partner/bills/pending', [BillController::class, 'pending']);
    Route::get('/partner/bills/confirmed', [BillController::class, 'confirmed']);
    Route::get('/partner/bills/{bill}', [BillController::class, 'show'])->whereNumber('bill');
    Route::post('/partner/bills/{bill}/mark-in-job', [BillController::class, 'markInJob'])->whereNumber('bill');
    Route::post('/partner/bills/{bill}/complete', [BillController::class, 'complete'])->whereNumber('bill');
});
