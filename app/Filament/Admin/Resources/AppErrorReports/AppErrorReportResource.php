<?php

namespace App\Filament\Admin\Resources\AppErrorReports;

use App\Enum\NavigationGroup;
use App\Filament\Admin\Resources\AppErrorReports\Pages\ListAppErrorReports;
use App\Filament\Admin\Resources\AppErrorReports\Pages\ViewAppErrorReport;
use App\Filament\Admin\Resources\AppErrorReports\Schemas\AppErrorReportInfolist;
use App\Filament\Admin\Resources\AppErrorReports\Tables\AppErrorReportsTable;
use App\Models\AppErrorReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AppErrorReportResource extends Resource
{
    protected static ?string $model = AppErrorReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::SYSTEM;

    protected static ?string $navigationLabel = 'Lỗi từ ứng dụng';

    protected static ?string $modelLabel = 'báo cáo lỗi';

    protected static ?string $pluralModelLabel = 'báo cáo lỗi';

    public static function infolist(Schema $schema): Schema
    {
        return AppErrorReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppErrorReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'checkedBy']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppErrorReports::route('/'),
            'view' => ViewAppErrorReport::route('/{record}'),
        ];
    }
}
