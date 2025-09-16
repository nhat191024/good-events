<?php

namespace App\Filament\Partner\Resources\PartnerBills;

use App\Models\PartnerBill;

use BackedEnum;
use UnitEnum;

use App\Filament\Partner\Resources\PartnerBills\Pages\CreatePartnerBill;
use App\Filament\Partner\Resources\PartnerBills\Pages\EditPartnerBill;
use App\Filament\Partner\Resources\PartnerBills\Pages\ListPartnerBills;
use App\Filament\Partner\Resources\PartnerBills\Schemas\PartnerBillForm;
use App\Filament\Partner\Resources\PartnerBills\Tables\PartnerBillsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class PartnerBillResource extends Resource
{
    protected static ?string $model = PartnerBill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    public static function getModelLabel(): string
    {
        return __('partner\bill.bill');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerBillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerBillsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('partner_id', auth()->id())
            ->where('status', 'pending')
            ->with(['client', 'category', 'event']);
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
            'index' => ListPartnerBills::route('/'),
            'create' => CreatePartnerBill::route('/create'),
            // 'edit' => EditPartnerBill::route('/{record}/edit'),
        ];
    }
}
