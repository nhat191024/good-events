<?php

namespace App\Filament\Admin\Resources\Partners;

use App\Models\User;

use BackedEnum;

use App\Filament\Admin\Resources\Partners\Pages\CreatePartner;
use App\Filament\Admin\Resources\Partners\Pages\EditPartner;
use App\Filament\Admin\Resources\Partners\Pages\ListPartners;
use App\Filament\Admin\Resources\Partners\Schemas\PartnerForm;
use App\Filament\Admin\Resources\Partners\Tables\PartnersTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\Role;

class PartnerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    public static function getModelLabel(): string
    {
        return __('admin\partner.partner');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('partnerProfile', 'roles')
            ->whereHas('roles', function ($query) {
                $query->where('name', Role::PARTNER);
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartners::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
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
