<?php

use App\Filament\Partner\Pages\CalendarPage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Calendar API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/calendar/events', [CalendarPage::class, 'getEvents'])->name('calendar.events');
    Route::get('/api/calendar/locale', [CalendarPage::class, 'getLocaleData'])->name('calendar.locale');
});

// Language switcher for testing
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
