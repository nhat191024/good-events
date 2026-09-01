<?php

namespace App\Filament\Admin\Resources\Accessories;

use App\Enum\FilamentNavigationGroup;
use App\Filament\Admin\Resources\Accessories\Pages\CreateAccessory;
use App\Filament\Admin\Resources\Accessories\Pages\EditAccessory;
use App\Filament\Admin\Resources\Accessories\Pages\ListAccessories;
use App\Filament\Admin\Resources\Accessories\Schemas\AccessoryForm;
use App\Filament\Admin\Resources\Accessories\Tables\AccessoriesTable;
use App\Models\Accessory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccessoryResource extends Resource
{
    protected static ?string $model = Accessory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::CATEGORIES;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin/accessory.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/accessory.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return AccessoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessoriesTable::configure($table);
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
            'index' => ListAccessories::route('/'),
            // 'create' => CreateAccessory::route('/create'),
            // 'edit' => EditAccessory::route('/{record}/edit'),
        ];
    }
}
