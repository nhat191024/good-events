<?php

namespace App\Filament\Admin\Resources\RevenueStatistics;

use App\Enum\FilamentNavigationGroup;
use App\Filament\Admin\Resources\RevenueStatistics\Pages\ListRevenueStatistics;
use App\Filament\Admin\Resources\RevenueStatistics\Tables\RevenueStatisticsTable;
use App\Models\RevenueRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RevenueStatisticResource extends Resource
{
    protected static ?string $model = RevenueRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BILLING;

    protected static ?string $slug = 'revenue-statistics';

    public static function getModelLabel(): string
    {
        return __('admin/revenue_statistic.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/revenue_statistic.plural_model_label');
    }

    public static function table(Table $table): Table
    {
        return RevenueStatisticsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return RevenueRecord::unionQuery();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRevenueStatistics::route('/'),
        ];
    }
}
