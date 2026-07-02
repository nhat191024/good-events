<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations;

use App\Enum\NavigationGroup;
use App\Enum\PartnerServiceStatus;
use App\Filament\Admin\Resources\ServiceRegistrations\Pages\CreateServiceRegistration;
use App\Filament\Admin\Resources\ServiceRegistrations\Pages\EditServiceRegistration;
use App\Filament\Admin\Resources\ServiceRegistrations\Pages\ListServiceRegistrations;
use App\Filament\Admin\Resources\ServiceRegistrations\Schemas\ServiceRegistrationForm;
use App\Filament\Admin\Resources\ServiceRegistrations\Tables\ServiceRegistrationsTable;
use App\Models\PartnerService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ServiceRegistrationResource extends Resource
{
    protected static ?string $model = PartnerService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM;
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('admin/partnerService.singular');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::$model::query()
            ->where('status', '=', PartnerServiceStatus::PENDING->value)
            ->count();

        return $count > 0 ? (string) $count : null;
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
            ->orderByRaw('case when status = ? then 0 else 1 end', [PartnerServiceStatus::PENDING->value])
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
