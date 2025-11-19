<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations;

use App\Models\PartnerService;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\ServiceRegistrations\Pages\CreateServiceRegistration;
use App\Filament\Admin\Resources\ServiceRegistrations\Pages\EditServiceRegistration;
use App\Filament\Admin\Resources\ServiceRegistrations\Pages\ListServiceRegistrations;
use App\Filament\Admin\Resources\ServiceRegistrations\Schemas\ServiceRegistrationForm;
use App\Filament\Admin\Resources\ServiceRegistrations\Tables\ServiceRegistrationsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

use App\Enum\NavigationGroup;

class ServiceRegistrationResource extends Resource
{
    protected static ?string $model = PartnerService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM->value;
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('admin/partnerService.singular');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::$model::query()
            ->where('status', '=', 'pending')
            ->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceRegistrationsTable::configure($table);
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
            ->with(['user', 'category', 'serviceMedia'])
            ->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRegistrations::route('/'),
            'create' => CreateServiceRegistration::route('/create'),
            'edit' => EditServiceRegistration::route('/{record}/edit'),
        ];
    }
}
