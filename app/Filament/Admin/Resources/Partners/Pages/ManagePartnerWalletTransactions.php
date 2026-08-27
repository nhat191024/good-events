<?php

namespace App\Filament\Admin\Resources\Partners\Pages;

use App\Filament\Admin\Resources\Partners\PartnerResource;
use App\Filament\Partner\Resources\Wallets\Tables\WalletsTable;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManagePartnerWalletTransactions extends ManageRelatedRecords
{
    protected static string $resource = PartnerResource::class;

    protected static string $relationship = 'transactions';

    public function getTitle(): string
    {
        return __('admin/partner.wallet_transactions_title', [
            'name' => $this->getRecord()->name,
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            PartnerResource::getIndexUrl() => PartnerResource::getModelLabel(),
            $this->getRecord()->name,
            __('admin/partner.actions.wallet_transactions'),
        ];
    }

    public function table(Table $table): Table
    {
        return WalletsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('confirmed', true)
                ->latest());
    }
}
