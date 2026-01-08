<?php

use App\Http\Controllers\Api\PartnerCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/partner-categories/{slug}', [PartnerCategoryController::class, 'show']);
});
