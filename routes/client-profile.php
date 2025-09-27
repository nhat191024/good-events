<?php

use App\Http\Controllers\Profile\ClientProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profile/{user}', [ClientProfileController::class, 'show'])
    ->whereNumber('user') // id
    ->name('profile.client.show');
