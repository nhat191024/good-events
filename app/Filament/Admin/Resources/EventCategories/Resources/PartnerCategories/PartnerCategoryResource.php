<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories;

use App\Models\PartnerCategory;

use BackedEnum;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;

use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages\CreatePartnerCategory;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Pages\EditPartnerCategory;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Schemas\PartnerCategoryForm;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Tables\PartnerCategoriesTable;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

use Filament\Resources\Resource;
use Filament\Resources\ParentResourceRegistration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartnerCategoryResource extends Resource
{
    protected static ?string $model = PartnerCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = EventCategoryResource::class;

    public static function getPluralModelLabel(): string
    {
        return __('admin/partnerCategory.partner_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerCategoriesTable::configure($table);
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return EventCategoryResource::asParent()
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
            'create' => CreatePartnerCategory::route('/create'),
            'edit' => EditPartnerCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withExists('partnerServices')
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
