<?php

use App\Http\Controllers\Api\Client\QuickBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/quick-booking/categories', [QuickBookingController::class, 'chooseCategory']);
Route::get('/quick-booking/{partnerCategorySlug}/children', [QuickBookingController::class, 'choosePartnerCategory']);
Route::get('/quick-booking/{partnerCategorySlug}/{partnerChildCategorySlug}/form', [QuickBookingController::class, 'fillOrderInfo']);
Route::post('/quick-booking/submit', [QuickBookingController::class, 'saveBookingInfo']);
Route::get('/quick-booking/finish/{billCode}', [QuickBookingController::class, 'finishedBooking']);
