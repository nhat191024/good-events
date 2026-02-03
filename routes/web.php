<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

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

require __DIR__ . '/socialite.php';

require __DIR__ . '/partner-profile.php';
require __DIR__ . '/client-profile.php';

// require __DIR__ . '/client/test-partner.php';
require __DIR__ . '/client/quick-booking.php';
require __DIR__ . '/client/order-history.php';
require __DIR__ . '/client/chat.php';
require __DIR__ . '/client/settings.php';
require __DIR__ . '/client/test-partner.php';
require __DIR__ . '/client/notification.php';
require __DIR__ . '/client/report.php';

require __DIR__ . '/test.php';

Route::get('robots.txt', function () {
    $robots = "User-agent: *\n";

    if (App::environment('production')) {
        $robots .= "Allow: /\n";
        $robots .= "Sitemap: " . url('sitemap.xml') . "\n";
    } else {
        // Chặn tất cả nếu không phải bản Production
        $robots .= "Disallow: /\n";
    }

    return response($robots, 200)->header('Content-Type', 'text/plain');
});
