<?php

namespace App\Filament\Partner\Resources\Wallets\Pages;

use App\Filament\Partner\Resources\Wallets\WalletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWallet extends EditRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
