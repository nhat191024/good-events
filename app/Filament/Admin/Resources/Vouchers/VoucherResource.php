<?php

namespace App\Filament\Admin\Resources\Vouchers;

use BeyondCode\Vouchers\Models\Voucher;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\Vouchers\Pages\CreateVoucher;
use App\Filament\Admin\Resources\Vouchers\Pages\EditVoucher;
use App\Filament\Admin\Resources\Vouchers\Pages\ListVouchers;
use App\Filament\Admin\Resources\Vouchers\Schemas\VoucherForm;
use App\Filament\Admin\Resources\Vouchers\Tables\VouchersTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

use App\Enum\NavigationGroup;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;
        protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM;

    public static function getModelLabel(): string
    {
        return __('admin/voucher.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return VoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VouchersTable::configure($table);
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
            'index' => ListVouchers::route('/'),
            'create' => CreateVoucher::route('/create'),
            'edit' => EditVoucher::route('/{record}/edit'),
        ];
    }
}
