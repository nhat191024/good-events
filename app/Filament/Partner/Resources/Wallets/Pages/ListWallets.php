<?php

namespace App\Filament\Partner\Resources\Wallets\Pages;

use App\Filament\Partner\Resources\Wallets\WalletResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_funds')
                ->label(__('partner/transaction.button.add_funds'))
                ->color('success')
                ->icon('heroicon-o-plus'),
        ];
    }
}
