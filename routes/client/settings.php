<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::middleware('auth')->group(callback: function () {
    Route::prefix("cai-dat")->group(
        function () {
            Route::redirect('/', '/cai-dat/ho-so');

            Route::prefix("ho-so")->name('profile.')->group(
                function () {

                    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
                    Route::patch('ho-so', [ProfileController::class, 'update'])->middleware('throttle:auth')->name('update');
                    Route::delete('ho-so', [ProfileController::class, 'destroy'])->middleware('throttle:auth')->name('destroy');

                    Route::get('mat-khau', [PasswordController::class, 'edit'])->name('password.edit');

                    Route::put('mat-khau', [PasswordController::class, 'update'])
                        ->middleware('throttle:auth')
                        ->name('password.update');

                }
            );

            Route::prefix("mat-khau")->name('password.')->group(
                function () {

                    Route::get('/', [PasswordController::class, 'edit'])->name('edit');

                    Route::put('/', [PasswordController::class, 'update'])
                        ->middleware('throttle:auth')
                        ->name('update');

                }
            );

            // Route::get('giao-dien', function () {
            //     return Inertia::render('settings/Appearance');
            // })->name('appearance');


            Route::prefix("dang-ky-nhan-su")->name('partner.register.from-client.')->group(
                function () {
                    Route::get('/', [RegisteredUserController::class, 'createPartnerFromClient'])->name('create');
                    Route::post('/', [RegisteredUserController::class, 'makePartner'])->middleware('throttle:auth')->name('store');
                }
            );

        }
    );

});
