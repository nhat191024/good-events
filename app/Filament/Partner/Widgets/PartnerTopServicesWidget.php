<?php

namespace App\Filament\Partner\Widgets;

use App\Models\PartnerCategory;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerTopServicesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected $popularCategories = null;

    protected function getTableHeading(): ?string
    {
        return 'Dịch vụ phổ biến';
    }

    protected function popularCategoryStatsQuery()
    {
        return DB::table('partner_bills')
            ->select([
                'category_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('MAX(created_at) as latest_order'),
            ])
            ->where('partner_id', Auth::id())
            ->where('status', '=', 'completed')
            ->groupBy('category_id');
    }

    protected function getPopularCategories()
    {
        if ($this->popularCategories === null) {
            $this->popularCategories = $this->popularCategoryStatsQuery()
                ->orderByDesc('order_count')
                ->limit(5)
                ->get()
                ->keyBy('category_id');
        }

        return $this->popularCategories;
    }

    public function table(Table $table): Table
    {
        $popularCategories = $this->getPopularCategories();
        $categoryIds = array_map('intval', $popularCategories->pluck('category_id')->toArray());

        if (empty($categoryIds)) {
            return $table
                ->query(PartnerCategory::query()->whereRaw('1 = 0'))
                ->columns($this->getTableColumns(sortable: false))
                ->emptyStateHeading('Chưa có dịch vụ phổ biến')
                ->emptyStateDescription('Các dịch vụ phổ biến sẽ hiển thị khi bạn có đơn hàng hoàn thành.')
                ->searchable(false)
                ->paginated(false);
        }

        return $table
            ->query(
                PartnerCategory::select([
                        'partner_categories.*',
                        'category_stats.order_count',
                        'category_stats.total_revenue',
                        'category_stats.latest_order',
                    ])
                    ->joinSub(
                        $this->popularCategoryStatsQuery()->whereIn('category_id', $categoryIds),
                        'category_stats',
                        fn ($join) => $join->on('category_stats.category_id', '=', 'partner_categories.id'),
                    )
                    ->orderByRaw('FIELD(partner_categories.id, ' . implode(',', $categoryIds) . ')')
            )
            ->columns($this->getTableColumns())
            ->emptyStateHeading('Chưa có dịch vụ phổ biến')
            ->emptyStateDescription('Các dịch vụ phổ biến sẽ hiển thị khi bạn có đơn hàng hoàn thành.')
            ->searchable(false)
            ->paginated(false);
    }

    protected function getTableColumns(bool $sortable = true): array
    {
        return [
            TextColumn::make('name')
                ->label('Danh mục dịch vụ')
                ->sortable($sortable),

            TextColumn::make('order_count')
                ->label('Số lần được đặt')
                ->badge()
                ->color('success')
                ->sortable($sortable),

            TextColumn::make('total_revenue')
                ->label('Tổng doanh thu')
                ->money('VND')
                ->color('primary')
                ->sortable($sortable),

            TextColumn::make('latest_order')
                ->label('Đơn gần nhất')
                ->dateTime('d/m/Y')
                ->sortable($sortable),
        ];
    }
}
