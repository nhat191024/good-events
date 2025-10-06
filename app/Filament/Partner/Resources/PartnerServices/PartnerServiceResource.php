<?php

namespace App\Filament\Partner\Resources\PartnerServices;

use App\Models\PartnerService;

use BackedEnum;

use App\Filament\Partner\Resources\PartnerServices\Pages\CreatePartnerService;
use App\Filament\Partner\Resources\PartnerServices\Pages\EditPartnerService;
use App\Filament\Partner\Resources\PartnerServices\Pages\ListPartnerServices;
use App\Filament\Partner\Resources\PartnerServices\Schemas\PartnerServiceForm;
use App\Filament\Partner\Resources\PartnerServices\Tables\PartnerServicesTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartnerServiceResource extends Resource
{
    protected static ?string $model = PartnerService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Square2Stack;

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('partner/service.your_services');
    }

    public static function form(Schema $schema): Schema
    {
        return PartnerServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerServicesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->with(['category', 'serviceMedia']);
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
            'index' => ListPartnerServices::route('/'),
            // 'create' => CreatePartnerService::route('/create'),
            // 'edit' => EditPartnerService::route('/{record}/edit'),
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
