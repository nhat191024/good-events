<?php

namespace App\Filament\Admin\Resources\DesignCategories;

use App\Models\Category;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\DesignCategories\Schemas\DesignCategoryForm;
use App\Filament\Admin\Resources\DesignCategories\Tables\DesignCategoriesTable;

use App\Filament\Admin\Resources\DesignCategories\Pages\CreateDesignCategory;
use App\Filament\Admin\Resources\DesignCategories\Pages\EditDesignCategory;
use App\Filament\Admin\Resources\DesignCategories\Pages\ListDesignCategories;

use App\Filament\Admin\Resources\DesignCategories\Pages\ManageDesignCategoryChildren;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class DesignCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::CATEGORIES;

    public static function getModelLabel(): string
    {
        return __('admin/category.singulars.design');
    }

    public static function form(Schema $schema): Schema
    {
        return DesignCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DesignCategoriesTable::configure($table);
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
            'index' => ListDesignCategories::route('/'),
            'create' => CreateDesignCategory::route('/create'),
            'edit' => EditDesignCategory::route('/{record}/edit'),
            'children-categories' => ManageDesignCategoryChildren::route('/{record}/children'),
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
            ->where('type', '=', 'design')
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
