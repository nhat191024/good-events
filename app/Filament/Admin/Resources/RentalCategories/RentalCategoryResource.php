<?php

namespace App\Filament\Admin\Resources\RentalCategories;

use App\Models\Category;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\RentalCategories\Pages\CreateRentalCategory;
use App\Filament\Admin\Resources\RentalCategories\Pages\EditRentalCategory;
use App\Filament\Admin\Resources\RentalCategories\Pages\ListRentalCategories;
use App\Filament\Admin\Resources\RentalCategories\Schemas\RentalCategoryForm;
use App\Filament\Admin\Resources\RentalCategories\Tables\RentalCategoriesTable;

use App\Filament\Admin\Resources\RentalCategories\Pages\ManageRentalCategoryChildren;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class RentalCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::CATEGORIES;

    public static function getModelLabel(): string
    {
        return __('admin/category.singulars.rental');
    }

    public static function form(Schema $schema): Schema
    {
        return RentalCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalCategoriesTable::configure($table);
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
            'index' => ListRentalCategories::route('/'),
            'create' => CreateRentalCategory::route('/create'),
            'edit' => EditRentalCategory::route('/{record}/edit'),
            'children-categories' => ManageRentalCategoryChildren::route('/{record}/children'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['media'])
            ->withExists('children')
            ->where('type', '=', 'rental')
            ->whereNull('parent_id');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
