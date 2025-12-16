<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/ve-chung-toi', [AboutController::class, 'index'])->name('about.index');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::get('/huong-dan', [App\Http\Controllers\TutorialController::class, 'index'])->name('tutorial.index');
Route::get('/huong-dan/khac', [App\Http\Controllers\TutorialController::class, 'other'])->name('tutorial.other');
Route::get('/privacy-policy', [StaticPageController::class, 'privacyPolicy'])->name('static.privacy');
Route::get('/shipping-policy-and-payment-methods', [StaticPageController::class, 'shippingPolicy'])->name('static.shipping');
Route::get('/terms-and-condition', [StaticPageController::class, 'termsAndConditions'])->name('static.terms');
Route::get('/faq', [StaticPageController::class, 'faq'])->name('static.faq');
