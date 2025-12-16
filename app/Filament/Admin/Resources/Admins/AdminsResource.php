<?php

namespace App\Filament\Admin\Resources\Admins;

use App\Models\User;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\Admins\Schemas\AdminsForm;
use App\Filament\Admin\Resources\Admins\Tables\AdminsTable;
use App\Filament\Admin\Resources\Admins\Pages\CreateAdmins;
use App\Filament\Admin\Resources\Admins\Pages\EditAdmins;
use App\Filament\Admin\Resources\Admins\Pages\ListAdmins;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

use App\Enum\Role;
use App\Enum\FilamentNavigationGroup;

class AdminsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::USER_MANAGEMENT;

    public static function getModelLabel(): string
    {
        return __('admin/admin.admin');
    }

    public static function form(Schema $schema): Schema
    {
        return AdminsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
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
            'index' => ListAdmins::route('/'),
            // 'create' => CreateAdmins::route('/create'),
            // 'edit' => EditAdmins::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    Role::ADMIN,
                    Role::HUMAN_RESOURCE_MANAGER,
                    Role::DESIGN_MANAGER,
                    Role::RENTAL_MANAGER
                ]);
            });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
