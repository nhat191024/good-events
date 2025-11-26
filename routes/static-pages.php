<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/ve-chung-toi', [AboutController::class, 'index'])->name('about.index');
Route::get('/lien-he', [ContactController::class, 'index'])->name('contact.index');
Route::get('/huong-dan', [App\Http\Controllers\TutorialController::class, 'index'])->name('tutorial.index');
Route::get('/huong-dan/khac', [App\Http\Controllers\TutorialController::class, 'other'])->name('tutorial.other');
