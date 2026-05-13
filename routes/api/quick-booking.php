<?php

use App\Http\Controllers\Api\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/quick-booking/event-list', [QuickBookingController::class, 'eventList']);
Route::middleware(['auth:sanctum', 'api.verified', 'throttle:api'])->group(function () {
    Route::post('/quick-booking/save', [QuickBookingController::class, 'saveBookingInfo']);
});
