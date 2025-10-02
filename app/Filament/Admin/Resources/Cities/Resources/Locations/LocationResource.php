<?php

namespace App\Filament\Admin\Resources\Cities\Resources\Locations;

use App\Filament\Admin\Resources\Cities\CityResource;
use App\Filament\Admin\Resources\Cities\Resources\Locations\Pages\CreateLocation;
use App\Filament\Admin\Resources\Cities\Resources\Locations\Pages\EditLocation;
use App\Filament\Admin\Resources\Cities\Resources\Locations\Schemas\LocationForm;
use App\Filament\Admin\Resources\Cities\Resources\Locations\Tables\LocationsTable;
use App\Models\Location;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = CityResource::class;

    public static function getModelLabel(): string
    {
        return __('admin/location.wards');
    }

    public static function form(Schema $schema): Schema
    {
        return LocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocationsTable::configure($table);
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
            'create' => CreateLocation::route('/create'),
            'edit' => EditLocation::route('/{record}/edit'),
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
