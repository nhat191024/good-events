<?php

use App\Http\Controllers\Api\Partner\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('wallet')->group(function () {
    Route::get('/transactions', [WalletController::class, 'transactions']);
    Route::post('/regenerate-add-funds-link', [WalletController::class, 'regenerateAddFundsLink']);
    Route::post('/confirm-add-funds', [WalletController::class, 'confirmAddFunds']);
});
