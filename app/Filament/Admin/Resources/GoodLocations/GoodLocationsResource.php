<?php

namespace App\Filament\Admin\Resources\GoodLocations;

use App\Models\Blog;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\GoodLocations\Schemas\GoodLocationsForm;
use App\Filament\Admin\Resources\GoodLocations\Tables\GoodLocationsTable;

use App\Filament\Admin\Resources\GoodLocations\Pages\CreateGoodLocations;
use App\Filament\Admin\Resources\GoodLocations\Pages\EditGoodLocations;
use App\Filament\Admin\Resources\GoodLocations\Pages\ListGoodLocations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class GoodLocationsResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BLOG;

    public static function getNavigationLabel(): string
    {
        return __('admin/blog.singulars.good_location');
    }

    public static function getModelLabel(): string
    {
        return __('admin/blog.plurals.good_location');
    }

    public static function form(Schema $schema): Schema
    {
        return GoodLocationsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GoodLocationsTable::configure($table);
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
            'index' => ListGoodLocations::route('/'),
            'create' => CreateGoodLocations::route('/create'),
            'edit' => EditGoodLocations::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'author', 'media', 'location.province'])
            ->whereType('good_location');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
