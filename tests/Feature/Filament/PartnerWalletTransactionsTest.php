<?php

use App\Filament\Admin\Resources\Partners\Pages\ManagePartnerWalletTransactions;
use App\Filament\Admin\Resources\Partners\PartnerResource;
use Illuminate\Support\Facades\Route;

it('registers the partner wallet transactions page', function (): void {
    expect(Route::has('filament.admin.resources.partners.wallet-transactions'))->toBeTrue()
        ->and(PartnerResource::getUrl('wallet-transactions', [
            'record' => 123,
        ], panel: 'admin'))->toEndWith('/admin/partners/123/wallet-transactions');
});

it('uses the partner transactions relationship', function (): void {
    $relationship = new ReflectionProperty(ManagePartnerWalletTransactions::class, 'relationship');

    expect($relationship->getValue())->toBe('transactions');
});
