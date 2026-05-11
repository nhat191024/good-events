<?php

use App\Http\Controllers\Api\Client\PartnerCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/partner-categories/{slug}', [PartnerCategoryController::class, 'show']);
Route::get('/partner-categories', [PartnerCategoryController::class, 'index']);
