<?php

namespace App\Filament\Admin\Resources\WalletHistories;

use App\Enum\FilamentNavigationGroup;
use App\Filament\Admin\Resources\WalletHistories\Pages\ListWalletHistories;
use App\Filament\Admin\Resources\WalletHistories\Tables\WalletHistoriesTable;
use BackedEnum;
use Bavix\Wallet\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WalletHistoryResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::USER_MANAGEMENT;

    public static function getModelLabel(): string
    {
        return __('admin/wallet_history.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/wallet_history.plural_model_label');
    }

    public static function table(Table $table): Table
    {
        return WalletHistoriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('payable')
            ->where('confirmed', true)
            ->orderByDesc('created_at');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWalletHistories::route('/'),
        ];
    }
}
