<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Partner\Pages\CalendarPage;

Route::middleware(['auth'])->group(function () {
    Route::get('/api/calendar/events', [CalendarPage::class, 'getEvents'])
        ->middleware('throttle:api')
        ->name('calendar.events');
    Route::get('/api/calendar/locale', [CalendarPage::class, 'getLocaleData'])->name('calendar.locale');
});
