<?php

use App\Http\Controllers\Home\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Add other routes as needed
Route::get('/category/{slug}', function ($slug) {
    // Handle category page
    return redirect('/'); // Temporary redirect
})->name('category.show');
