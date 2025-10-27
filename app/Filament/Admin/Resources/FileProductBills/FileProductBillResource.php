<?php

namespace App\Filament\Admin\Resources\FileProductBills;

use App\Models\FileProductBill;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\FileProductBills\Schemas\FileProductBillForm;
use App\Filament\Admin\Resources\FileProductBills\Tables\FileProductBillsTable;

use App\Filament\Admin\Resources\FileProductBills\Pages\CreateFileProductBill;
use App\Filament\Admin\Resources\FileProductBills\Pages\EditFileProductBill;
use App\Filament\Admin\Resources\FileProductBills\Pages\ListFileProductBills;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

use App\Enum\FilamentNavigationGroup;

class FileProductBillResource extends Resource
{
    protected static ?string $model = FileProductBill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BILLING;

    public static function getModelLabel(): string
    {
        return __('admin/fileProductBill.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return FileProductBillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FileProductBillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'fileProduct',
                'client',
            ])
            ->orderBy('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFileProductBills::route('/'),
            // 'create' => CreateFileProductBill::route('/create'),
            // 'edit' => EditFileProductBill::route('/{record}/edit'),
        ];
    }
}
