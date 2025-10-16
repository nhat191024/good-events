<?php

namespace App\Filament\Admin\Resources\PartnerBills;

use App\Models\PartnerBill;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\PartnerBills\Pages\CreatePartnerBill;
use App\Filament\Admin\Resources\PartnerBills\Pages\EditPartnerBill;
use App\Filament\Admin\Resources\PartnerBills\Pages\ListPartnerBills;
use App\Filament\Admin\Resources\PartnerBills\Schemas\PartnerBillForm;
use App\Filament\Admin\Resources\PartnerBills\Tables\PartnerBillsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

use App\Enum\FilamentNavigationGroup;

class PartnerBillResource extends Resource
{
    protected static ?string $model = PartnerBill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BILLING;

    public static function getModelLabel(): string
    {
        return __('admin/partnerBill.singular');
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
            ->with([
                'event',
                'client',
                'partner',
                'category',
            ])
            ->orderBy('updated_at', 'desc');
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
            // 'create' => CreatePartnerBill::route('/create'),
            // 'edit' => EditPartnerBill::route('/{record}/edit'),
        ];
    }
}
