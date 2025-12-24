<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteController;

Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');
