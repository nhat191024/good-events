<?php

use App\Http\Controllers\Api\Partner\PartnerCategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/category/index', [PartnerCategoryController::class, 'index']);
