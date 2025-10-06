<?php

namespace App\Filament\Admin\Resources\EventCategories;

use App\Models\PartnerCategory;

use UnitEnum;
use BackedEnum;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

use App\Filament\Admin\Resources\EventCategories\Tables\EventCategoriesTable;
use App\Filament\Admin\Resources\EventCategories\Schemas\EventCategoryForm;

use App\Filament\Admin\Resources\EventCategories\Pages\EditEventCategory;
use App\Filament\Admin\Resources\EventCategories\Pages\CreateEventCategory;
use App\Filament\Admin\Resources\EventCategories\Pages\ListEventCategories;

use App\Filament\Admin\Resources\EventCategories\Pages\ManagePartnerCategory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class EventCategoryResource extends Resource
{
    protected static ?string $model = PartnerCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::CATEGORIES;

    public static function getModelLabel(): string
    {
        return __('admin/partnerCategory.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return EventCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventCategoriesTable::configure($table);
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
            'index' => ListEventCategories::route('/'),
            'create' => CreateEventCategory::route('/create'),
            'edit' => EditEventCategory::route('/{record}/edit'),
            'partner-categories' => ManagePartnerCategory::route('/{record}/partner-categories'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withExists('children')
            ->whereNull('parent_id')
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
