<?php

namespace App\Filament\Partner\Resources\PartnerBillHistories;

use App\Models\PartnerBill;

use BackedEnum;

use App\Filament\Partner\Resources\PartnerBillHistories\Pages\CreatePartnerBillHistory;
use App\Filament\Partner\Resources\PartnerBillHistories\Pages\EditPartnerBillHistory;
use App\Filament\Partner\Resources\PartnerBillHistories\Pages\ListPartnerBillHistories;
use App\Filament\Partner\Resources\PartnerBillHistories\Schemas\PartnerBillHistoryForm;
use App\Filament\Partner\Resources\PartnerBillHistories\Tables\PartnerBillHistoriesTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class PartnerBillHistoryResource extends Resource
{
    protected static ?string $model = PartnerBill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('partner/bill.bill_history');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerBillHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerBillHistoriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('partner_id', auth()->id())
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
            'index' => ListPartnerBillHistories::route('/'),
            // 'create' => CreatePartnerBillHistory::route('/create'),
            // 'view' => ViewPartnerBillHistory::route('/{record}'),
            // 'edit' => EditPartnerBillHistory::route('/{record}/edit'),
        ];
    }
}
