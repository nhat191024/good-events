<?php

namespace App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens;

use App\Models\Category;

use BackedEnum;

use App\Filament\Admin\Resources\BlogCategories\BlogCategoryResource;

use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Schemas\BlogCategoryChildrenForm;
use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Tables\BlogCategoryChildrensTable;

use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Pages\CreateBlogCategoryChildren;
use App\Filament\Admin\Resources\BlogCategories\Resources\BlogCategoryChildrens\Pages\EditBlogCategoryChildren;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\ParentResourceRegistration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogCategoryChildrenResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = BlogCategoryResource::class;

    public static function getModelLabel(): string
    {
        return __('admin/category.children_category');
    }

    public static function form(Schema $schema): Schema
    {
        return BlogCategoryChildrenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogCategoryChildrensTable::configure($table);
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return BlogCategoryResource::asParent()
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
            'create' => CreateBlogCategoryChildren::route('/create'),
            'edit' => EditBlogCategoryChildren::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
