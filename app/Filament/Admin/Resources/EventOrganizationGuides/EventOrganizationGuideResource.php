<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides;

use App\Models\EventOrganizationGuide;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\EventOrganizationGuides\Schemas\EventOrganizationGuideForm;
use App\Filament\Admin\Resources\EventOrganizationGuides\Tables\EventOrganizationGuidesTable;

use App\Filament\Admin\Resources\EventOrganizationGuides\Pages\CreateEventOrganizationGuide;
use App\Filament\Admin\Resources\EventOrganizationGuides\Pages\EditEventOrganizationGuide;
use App\Filament\Admin\Resources\EventOrganizationGuides\Pages\ListEventOrganizationGuides;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class EventOrganizationGuideResource extends Resource
{
    protected static ?string $model = EventOrganizationGuide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BLOG;

    public static function getNavigationLabel(): string
    {
        return __('admin/blog.singulars.event_organization_guide');
    }

    public static function getModelLabel(): string
    {
        return __('admin/blog.plurals.event_organization_guide');
    }

    public static function form(Schema $schema): Schema
    {
        return EventOrganizationGuideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventOrganizationGuidesTable::configure($table);
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
            'index' => ListEventOrganizationGuides::route('/'),
            'create' => CreateEventOrganizationGuide::route('/create'),
            'edit' => EditEventOrganizationGuide::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'author'])
            ->whereType('event_organization_guide');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
