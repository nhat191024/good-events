<?php

use App\Http\Controllers\TestPartnerCategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/client/quick-booking.php';
require __DIR__.'/client/order.php';
require __DIR__.'/client/chat.php';
require __DIR__.'/client/test-partner.php';
