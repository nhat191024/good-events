<?php

namespace App\Filament\Partner\Resources\Wallets;

use Bavix\Wallet\Models\Transaction;
use BackedEnum;

use App\Filament\Partner\Resources\Wallets\Pages\CreateWallet;
use App\Filament\Partner\Resources\Wallets\Pages\EditWallet;
use App\Filament\Partner\Resources\Wallets\Pages\ListWallets;
use App\Filament\Partner\Resources\Wallets\Schemas\WalletForm;
use App\Filament\Partner\Resources\Wallets\Tables\WalletsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class WalletResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return __('partner/transaction.wallet');
    }

    public static function form(Schema $schema): Schema
    {
        return WalletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('payable_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWallets::route('/'),
            // 'create' => CreateWallet::route('/create'),
            // 'edit' => EditWallet::route('/{record}/edit'),
        ];
    }
}
