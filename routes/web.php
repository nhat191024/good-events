<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PaymentController;

Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/api/calendar.php';

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
require __DIR__ . '/static-pages.php';



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
