<?php

use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Health check (public)
Route::get('/health', [HealthController::class, 'health']);
Route::get('/ping', [HealthController::class, 'ping']);
Route::get('/csrf-token', fn() => csrf_token()); // https://stackoverflow.com/a/31451123

// Public & mixed-auth routes (each file manages its own internal auth groups)
require __DIR__ . '/api/location.php';
require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/blog.php';
require __DIR__ . '/api/rental.php';
require __DIR__ . '/api/partner-categories.php';
require __DIR__ . '/api/asset.php';
require __DIR__ . '/api/profile.php';
require __DIR__ . '/api/setting.php';

// Authenticated client routes
Route::middleware(['auth:sanctum', 'api.verified'])->group(function () {
    require __DIR__ . '/api/client/home.php';
    require __DIR__ . '/api/quick-booking.php';
    require __DIR__ . '/api/orders.php';
    require __DIR__ . '/api/asset-orders.php';
    require __DIR__ . '/api/chat.php';
    require __DIR__ . '/api/notifications.php';
    require __DIR__ . '/api/reports.php';
    require __DIR__ . '/api/resources.php';
    require __DIR__ . '/api/FCM.php';
});

// Authenticated partner routes
Route::middleware(['auth:sanctum', 'api.verified'])->prefix('partner')->group(function () {
    require __DIR__ . '/api/partner/dashboard.php';
    require __DIR__ . '/api/partner/analytics.php';
    require __DIR__ . '/api/partner/bills.php';
    require __DIR__ . '/api/partner/calendar.php';
    require __DIR__ . '/api/partner/service.php';
    require __DIR__ . '/api/partner/category.php';
    require __DIR__ . '/api/partner/wallet.php';
});

// Broadcasting auth endpoint for Sanctum API token authentication
// Registers POST /api/broadcasting/auth
Broadcast::routes(['middleware' => ['auth:sanctum', 'api.verified']]);
