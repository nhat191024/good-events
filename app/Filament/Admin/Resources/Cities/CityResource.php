<?php

namespace App\Filament\Admin\Resources\Cities;

use App\Models\Location;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\Cities\Pages\CreateCity;
use App\Filament\Admin\Resources\Cities\Pages\EditCity;
use App\Filament\Admin\Resources\Cities\Pages\ListCities;
use App\Filament\Admin\Resources\Cities\Schemas\CityForm;
use App\Filament\Admin\Resources\Cities\Tables\CitiesTable;

use App\Filament\Admin\Resources\Cities\Pages\ManageWard;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\NavigationGroup;

use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CityResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Location::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM->value;

    public static function getModelLabel(): string
    {
        return __('admin/location.singular');
    }
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return CityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_id')
            ->withExists('wards')
            ->withTrashed();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
            // 'create' => CreateCity::route('/create'),
            // 'edit' => EditCity::route('/{record}/edit'),
            'wards' => ManageWard::route('/{record}/wards'),
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
