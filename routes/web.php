<?php
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PaymentController;

use App\Filament\Partner\Pages\CalendarPage;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

// Calendar API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/calendar/events', [CalendarPage::class, 'getEvents'])->name('calendar.events');
    Route::get('/api/calendar/locale', [CalendarPage::class, 'getLocaleData'])->name('calendar.locale');

    Route::get('/payment/result', [PaymentController::class, 'result'])->name('payment.result');
});

// Language switcher for testing
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

require __DIR__ . '/auth.php';

require __DIR__ . '/home.php';

require __DIR__ . '/partner-profile.php';
require __DIR__ . '/client-profile.php';

// require __DIR__ . '/client/test-partner.php';
require __DIR__ . '/client/quick-booking.php';
require __DIR__ . '/client/order-history.php';
require __DIR__ . '/client/chat.php';
require __DIR__ . '/client/settings.php';
require __DIR__ . '/client/test-partner.php';
require __DIR__ . '/client/notification.php';

require __DIR__ . '/test.php';
