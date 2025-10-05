<?php

use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\PartnerCategory\PartnerCategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/home', [HomeController::class, 'index'])->name('home-page');

// Trang “Danh mục cha”: slug là slug của category KHÔNG có parent_id
Route::get('/categories/{slug}', [CategoryController::class, 'showParent'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('categories.parent');

Route::get('/partner-categories/{slug}', [PartnerCategoryController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('partner-categories.show');
