<?php

namespace App\Filament\Admin\Resources\Reports;

use App\Models\Report;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\Reports\Pages\CreateReport;
use App\Filament\Admin\Resources\Reports\Pages\EditReport;
use App\Filament\Admin\Resources\Reports\Pages\ListReports;
use App\Filament\Admin\Resources\Reports\Pages\ViewReport;
use App\Filament\Admin\Resources\Reports\Schemas\ReportForm;
use App\Filament\Admin\Resources\Reports\Schemas\ReportInfolist;
use App\Filament\Admin\Resources\Reports\Tables\ReportsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

use App\Enum\NavigationGroup;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationCircle;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM;

    public static function getModelLabel(): string
    {
        return __('admin/report.singular');
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
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
            ->with(['user', 'reportedUser', 'reportedBill']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            // 'create' => CreateReport::route('/create'),
            // 'view' => ViewReport::route('/{record}'),
            // 'edit' => EditReport::route('/{record}/edit'),
        ];
    }
}
