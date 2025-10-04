<?php

use App\Http\Controllers\TestPartnerCategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/partner-categories/create', [TestPartnerCategoryController::class, 'create'])
    ->name('partner-categories.create');

Route::post('/partner-categories', [TestPartnerCategoryController::class, 'store'])
    ->name('partner-categories.store');

// route để xóa media (spatie media id)
Route::delete('/media/{id}', [TestPartnerCategoryController::class, 'destroyMedia'])
    ->name('media.destroy');