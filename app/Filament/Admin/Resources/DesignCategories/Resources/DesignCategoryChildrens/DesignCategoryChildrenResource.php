<?php

namespace App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens;

use App\Models\Category;

use BackedEnum;

use App\Filament\Admin\Resources\DesignCategories\DesignCategoryResource;

use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Schemas\DesignCategoryChildrenForm;
use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Tables\DesignCategoryChildrensTable;

use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Pages\CreateDesignCategoryChildren;
use App\Filament\Admin\Resources\DesignCategories\Resources\DesignCategoryChildrens\Pages\EditDesignCategoryChildren;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\ParentResourceRegistration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DesignCategoryChildrenResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = DesignCategoryResource::class;

    public static function getModelLabel(): string
    {
        return __('admin/category.children_category');
    }

    public static function form(Schema $schema): Schema
    {
        return DesignCategoryChildrenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DesignCategoryChildrensTable::configure($table);
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return DesignCategoryResource::asParent()
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
            'create' => CreateDesignCategoryChildren::route('/create'),
            'edit' => EditDesignCategoryChildren::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
