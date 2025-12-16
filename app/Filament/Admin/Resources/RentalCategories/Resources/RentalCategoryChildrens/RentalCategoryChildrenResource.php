<?php

namespace App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens;

use App\Models\Category;

use BackedEnum;

use App\Filament\Admin\Resources\RentalCategories\RentalCategoryResource;

use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Schemas\RentalCategoryChildrenForm;
use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Tables\RentalCategoryChildrensTable;

use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Pages\CreateRentalCategoryChildren;
use App\Filament\Admin\Resources\RentalCategories\Resources\RentalCategoryChildrens\Pages\EditRentalCategoryChildren;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\ParentResourceRegistration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RentalCategoryChildrenResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = RentalCategoryResource::class;

    public static function getModelLabel(): string
    {
        return __('admin/category.children_category');
    }

    public static function form(Schema $schema): Schema
    {
        return RentalCategoryChildrenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalCategoryChildrensTable::configure($table);
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return RentalCategoryResource::asParent()
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
            'create' => CreateRentalCategoryChildren::route('/create'),
            'edit' => EditRentalCategoryChildren::route('/{record}/edit'),
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
