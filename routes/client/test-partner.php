<?php

use App\Http\Controllers\TestPartnerCategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/test/partner-categories/create', [TestPartnerCategoryController::class, 'create'])
    ->name('partner-categories.create');

Route::post('/test/partner-categories', [TestPartnerCategoryController::class, 'store'])
    ->name('partner-categories.store');

// route để xóa media (spatie media id)
Route::delete('/test/media/{id}', [TestPartnerCategoryController::class, 'destroyMedia'])
    ->name('media.destroy');