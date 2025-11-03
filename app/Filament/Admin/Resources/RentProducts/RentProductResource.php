<?php

namespace App\Filament\Admin\Resources\RentProducts;

use App\Models\RentProduct;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\RentProducts\Schemas\RentProductForm;
use App\Filament\Admin\Resources\RentProducts\Tables\RentProductsTable;

use App\Filament\Admin\Resources\RentProducts\Pages\CreateRentProduct;
use App\Filament\Admin\Resources\RentProducts\Pages\EditRentProduct;
use App\Filament\Admin\Resources\RentProducts\Pages\ListRentProducts;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class RentProductResource extends Resource
{
    protected static ?string $model = RentProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::PRODUCTS;

    public static function getModelLabel(): string
    {
        return __('admin/rentProduct.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return RentProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentProductsTable::configure($table);
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
            'index' => ListRentProducts::route('/'),
            'create' => CreateRentProduct::route('/create'),
            'edit' => EditRentProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'media']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
