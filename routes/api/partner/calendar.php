<?php

use App\Http\Controllers\Api\Partner\CalendarController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/partner/calendar/events', [CalendarController::class, 'events']);
    Route::get('/partner/calendar/locale', [CalendarController::class, 'locale']);
});
