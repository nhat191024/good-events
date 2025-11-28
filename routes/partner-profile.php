<?php

use App\Http\Controllers\Profile\PartnerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profile/partner/{user}.json', [PartnerProfileController::class, 'showJson'])
    ->whereNumber('user')
    ->name('profile.partner.show.json');
