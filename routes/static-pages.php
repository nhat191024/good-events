<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/ve-chung-toi', [AboutController::class, 'index'])->name('about.index');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
