<?php

namespace App\Filament\Admin\Resources\FileProducts;

use App\Models\FileProduct;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\FileProducts\Schemas\FileProductForm;
use App\Filament\Admin\Resources\FileProducts\Tables\FileProductsTable;

use App\Filament\Admin\Resources\FileProducts\Pages\CreateFileProduct;
use App\Filament\Admin\Resources\FileProducts\Pages\EditFileProduct;
use App\Filament\Admin\Resources\FileProducts\Pages\ListFileProducts;
use App\Filament\Admin\Resources\FileProducts\Pages\ManageFileProductDesigns;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class FileProductResource extends Resource
{
    protected static ?string $model = FileProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::PRODUCTS;

    public static function getModelLabel(): string
    {
        return __('admin/fileProduct.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return FileProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FileProductsTable::configure($table);
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
            'index' => ListFileProducts::route('/'),
            'create' => CreateFileProduct::route('/create'),
            'edit' => EditFileProduct::route('/{record}/edit'),
            'manage-designs' => ManageFileProductDesigns::route('/{record}/manage-designs'),
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
