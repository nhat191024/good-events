<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\TutorialController;
use Illuminate\Support\Facades\Route;

Route::get('/ve-chung-toi', [AboutController::class, 'index'])->name('about.index');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::get('/huong-dan', [TutorialController::class, 'index'])->name('tutorial.index');
Route::get('/huong-dan/khach-hang', [TutorialController::class, 'client'])->name('tutorial.client');
Route::get('/huong-dan/doi-tac', [TutorialController::class, 'partner'])->name('tutorial.partner');
Route::get('/privacy-policy', [StaticPageController::class, 'privacyPolicy'])->name('static.privacy');
Route::get('/shipping-policy-and-payment-methods', [StaticPageController::class, 'shippingPolicy'])->name('static.shipping');
Route::get('/terms-and-condition', [StaticPageController::class, 'termsAndConditions'])->name('static.terms');
Route::get('/faq', [StaticPageController::class, 'faq'])->name('static.faq');
Route::get('/tai-app', [StaticPageController::class, 'downloadApp'])->name('static.download-app');
