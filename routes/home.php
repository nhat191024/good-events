<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\FileProductController;
use App\Http\Controllers\Home\AssetHomeController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\RentHomeController;
use App\Http\Controllers\PartnerCategory\PartnerCategoryController;
use App\Http\Controllers\RentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/api/home/event-categories', [HomeController::class, 'loadMoreCategories'])
    ->name('home.event-categories');

Route::get('/su-kien/danh-muc/{category_slug}', [HomeController::class, 'showCategory'])
    ->where('category_slug', '[A-Za-z0-9-]+')
    ->name('home.category');

// Trang “Danh mục cha”: slug là slug của category KHÔNG có parent_id
Route::get('/danh-muc/{slug}', [CategoryController::class, 'showParent'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('categories.parent');

Route::get('/danh-muc-su-kien/chi-tiet/{slug}', [PartnerCategoryController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')
    ->name('partner-categories.show');

Route::prefix('/tai-lieu')->name('asset.')->group(function () {
    Route::get('/', [AssetHomeController::class, 'index'])->name('home');
    Route::get('/kham-pha', [FileProductController::class, 'assetDiscover'])->name('discover');
    Route::get('/kham-pha/danh-muc/{category_slug}', [FileProductController::class, 'assetCategory'])->name('category');
    Route::get('/kham-pha/danh-muc/{category_slug}/chi-tiet/{file_product_slug}', [FileProductController::class, 'assetDetail'])->name('show');
    Route::get('/thanh-toan/{slug}', [FileProductController::class, 'assetPurchase'])->name('buy');
    Route::post('/thanh-toan', [FileProductController::class, 'assetConfirmPurchase'])->name('buy.confirm');
});

Route::prefix('/thue-vat-tu')->name('rent.')->group(function () {
    Route::get('/', [RentHomeController::class, 'index'])->name('home');
    Route::get('/kham-pha', [RentController::class, 'rentDiscover'])->name('discover');
    Route::get('/kham-pha/danh-muc/{category_slug}', [RentController::class, 'rentCategory'])->name('category');
    Route::get('/kham-pha/danh-muc/{category_slug}/chi-tiet/{rent_product_slug}', [RentController::class, 'rentDetail'])->name('show');
});

Route::prefix('/dia-diem-to-chuc-su-kien')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'blogDiscover'])->name('discover');
    Route::get('/danh-muc/{category_slug}', [BlogController::class, 'blogCategory'])->name('category');
    Route::get('/danh-muc/{category_slug}/{blog_slug}', [BlogController::class, 'blogDetail'])->name('show');
});
