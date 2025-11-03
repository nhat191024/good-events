<?php

namespace App\Filament\Partner\Resources\PartnerBills;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;

use BackedEnum;
use UnitEnum;

use App\Filament\Partner\Resources\PartnerBills\Pages\CreatePartnerBill;
use App\Filament\Partner\Resources\PartnerBills\Pages\EditPartnerBill;
use App\Filament\Partner\Resources\PartnerBills\Pages\ListPartnerBills;
use App\Filament\Partner\Resources\PartnerBills\Pages\PartnerBillsListPage;
use App\Filament\Partner\Resources\PartnerBills\Pages\ViewPartnerBill;
use App\Filament\Partner\Resources\PartnerBills\Schemas\PartnerBillForm;
use App\Filament\Partner\Resources\PartnerBills\Schemas\PartnerBillInfolist;
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
        return __('partner/bill.bill');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerBillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerBillsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PartnerBillInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB,
            ])
            ->whereHas(
                'details',
                function (Builder $query) {
                    $query->where('partner_id', auth()->id());
                }
            )
            ->with(['client', 'category', 'event'])
            ->orderByDesc('updated_at');
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
            'index' => PartnerBillsListPage::route('/'),
            'view' => ViewPartnerBill::route('/{record}'),
            // 'create' => CreatePartnerBill::route('/create'),
            // 'edit' => EditPartnerBill::route('/{record}/edit'),
        ];
    }
}
