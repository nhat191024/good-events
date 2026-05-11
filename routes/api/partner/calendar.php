<?php

use App\Http\Controllers\Api\Partner\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/calendar/events', [CalendarController::class, 'events']);
Route::get('/calendar/locale', [CalendarController::class, 'locale']);
