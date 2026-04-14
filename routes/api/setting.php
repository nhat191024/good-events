<?php

use App\Http\Controllers\Api\Common\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingController::class, 'getSettings']);
