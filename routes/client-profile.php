<?php

use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profile/{user}', [ProfileController::class, 'show'])
    ->whereNumber('user')
    ->name('profile.client.show');
