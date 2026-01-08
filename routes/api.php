<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HealthController;

// Health check routes (no authentication required)
Route::get('/health', [HealthController::class, 'health'])->middleware('throttle:api');
Route::get('/ping', [HealthController::class, 'ping'])->middleware('throttle:api');

//* https://stackoverflow.com/a/31451123
$router->get('csrf-token', function() {
    return csrf_token();
});

require __DIR__ .'/api/location.php';
require __DIR__ .'/api/auth.php';
require __DIR__ .'/api/home.php';
require __DIR__ .'/api/asset.php';
require __DIR__ .'/api/rental.php';
require __DIR__ .'/api/blog.php';
require __DIR__ .'/api/profile.php';
require __DIR__ .'/api/orders.php';
require __DIR__ .'/api/quick-booking.php';
require __DIR__ .'/api/asset-orders.php';
require __DIR__ .'/api/chat.php';
require __DIR__ .'/api/notifications.php';
require __DIR__ .'/api/reports.php';
require __DIR__ .'/api/partner-categories.php';
require __DIR__ .'/api/resources.php';
require __DIR__ .'/api/partner/dashboard.php';
require __DIR__ .'/api/partner/chat.php';
require __DIR__ .'/api/partner/bills.php';
require __DIR__ .'/api/partner/calendar.php';
require __DIR__ .'/api/partner/profile.php';
