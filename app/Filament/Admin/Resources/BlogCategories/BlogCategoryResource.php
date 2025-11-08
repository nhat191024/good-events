<?php

namespace App\Filament\Admin\Resources\BlogCategories;

use App\Models\Category;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\BlogCategories\Schemas\BlogCategoryForm;
use App\Filament\Admin\Resources\BlogCategories\Tables\BlogCategoriesTable;

use App\Filament\Admin\Resources\BlogCategories\Pages\CreateBlogCategory;
use App\Filament\Admin\Resources\BlogCategories\Pages\EditBlogCategory;
use App\Filament\Admin\Resources\BlogCategories\Pages\ListBlogCategories;

use App\Filament\Admin\Resources\BlogCategories\Pages\ManageBlogCategoryChildren;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::CATEGORIES;

    public static function getModelLabel(): string
    {
        return __('admin/category.blog_singular');
    }

    public static function form(Schema $schema): Schema
    {
        return BlogCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogCategoriesTable::configure($table);
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
            'index' => ListBlogCategories::route('/'),
            'create' => CreateBlogCategory::route('/create'),
            'edit' => EditBlogCategory::route('/{record}/edit'),
            'children-categories' => ManageBlogCategoryChildren::route('/{record}/children-categories'),
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
            ->where('type', '=', 'blog')
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
