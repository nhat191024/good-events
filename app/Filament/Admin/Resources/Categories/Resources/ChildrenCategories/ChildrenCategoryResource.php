<?php

namespace App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories;

use App\Models\Category;

use BackedEnum;

use App\Filament\Admin\Resources\Categories\CategoryResource;

use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Pages\CreateChildrenCategory;
use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Pages\EditChildrenCategory;
use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Schemas\ChildrenCategoryForm;
use App\Filament\Admin\Resources\Categories\Resources\ChildrenCategories\Tables\ChildrenCategoriesTable;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\ParentResourceRegistration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChildrenCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = CategoryResource::class;

    public static function getModelLabel(): string
    {
        return __('admin/category.children_category');
    }

    public static function form(Schema $schema): Schema
    {
        return ChildrenCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChildrenCategoriesTable::configure($table);
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return CategoryResource::asParent()
            ->relationship('children')
            ->inverseRelationship('parent');
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
            // 'create' => CreateChildrenCategory::route('/create'),
            // 'edit' => EditChildrenCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withTrashed();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
