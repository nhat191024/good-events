<?php

namespace App\Filament\Partner\Widgets;

use App\Models\PartnerBill;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PartnerTopServicesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    // Giảm tần suất polling
    protected ?string $pollingInterval = '60s';

    protected function getTableHeading(): ?string
    {
        return 'Dịch vụ phổ biến';
    }

    public function table(Table $table): Table
    {
        // Sử dụng raw query để tránh conflict với GROUP BY
        $popularCategories = DB::table('partner_bills')
            ->select([
                'category_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('MAX(created_at) as latest_order')
            ])
            ->where('partner_id', Auth::id())
            ->where('status', '!=', 'cancelled')
            ->groupBy('category_id')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get();

        $categoryIds = $popularCategories->pluck('category_id')->toArray();

        if (empty($categoryIds)) {
            // Nếu không có dữ liệu, trả về query rỗng
            return $table
                ->query(\App\Models\PartnerCategory::query()->whereRaw('1 = 0'))
                ->columns($this->getTableColumns())
                ->paginated(false);
        }

        return $table
            ->query(
                \App\Models\PartnerCategory::query()
                    ->whereIn('id', $categoryIds)
                    ->orderByRaw("FIELD(id, " . implode(',', $categoryIds) . ")")
            )
            ->columns($this->getTableColumns())
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        // Cache dữ liệu thống kê trong 15 phút
        $popularCategories = Cache::remember("partner_top_services_" . Auth::id(), 900, function () {
            return DB::table('partner_bills')
                ->select([
                    'category_id',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(final_total) as total_revenue'),
                    DB::raw('MAX(created_at) as latest_order')
                ])
                ->where('partner_id', Auth::id())
                ->where('status', '!=', 'cancelled')
                ->groupBy('category_id')
                ->orderBy('order_count', 'desc')
                ->limit(5)
                ->get()
                ->keyBy('category_id');
        });

        return [
            TextColumn::make('name')
                ->label('Danh mục dịch vụ')
                ->searchable()
                ->sortable(),

            TextColumn::make('order_count')
                ->label('Số lần được đặt')
                ->badge()
                ->color('success')
                ->getStateUsing(function ($record) use ($popularCategories) {
                    return $popularCategories->get($record->id)?->order_count ?? 0;
                }),

            TextColumn::make('total_revenue')
                ->label('Tổng doanh thu')
                ->money('VND')
                ->color('primary')
                ->getStateUsing(function ($record) use ($popularCategories) {
                    return $popularCategories->get($record->id)?->total_revenue ?? 0;
                }),

            TextColumn::make('latest_order')
                ->label('Đơn gần nhất')
                ->dateTime('d/m/Y')
                ->sortable()
                ->getStateUsing(function ($record) use ($popularCategories) {
                    return $popularCategories->get($record->id)?->latest_order ?? null;
                }),
        ];
    }
}
