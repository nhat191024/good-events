<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminTopPartnersWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected $topPartnersData;

    protected function getTableHeading(): ?string
    {
        return 'Top Partners - Doanh thu cao nhất';
    }

    public function table(Table $table): Table
    {
        // Lấy top 10 partners có doanh thu cao nhất
        $this->topPartnersData = Cache::remember('admin_top_partners', 900, function () {
            return DB::table('partner_bills')
                ->select([
                    'partner_id',
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(final_total) as total_revenue'),
                    DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_orders')
                ])
                ->whereNotNull('partner_id')
                ->whereIn('status', ['completed', 'confirmed', 'in_job'])
                ->groupBy('partner_id')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get()
                ->keyBy('partner_id');
        });

        $partnerIds = $this->topPartnersData->pluck('partner_id')->toArray();

        if (empty($partnerIds)) {
            return $table
                ->query(User::query()->whereRaw('1 = 0'))
                ->columns($this->getTableColumns())
                ->paginated(false);
        }

        return $table
            ->query(
                User::query()
                    ->whereIn('id', $partnerIds)
                    ->orderByRaw("FIELD(id, " . implode(',', $partnerIds) . ")")
            )
            ->columns($this->getTableColumns())
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Tên Partner')
                ->searchable()
                ->sortable()
                ->weight('bold'),

            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->copyable()
                ->copyMessage('Email đã được copy!')
                ->icon('heroicon-m-envelope'),

            TextColumn::make('total_orders')
                ->label('Số đơn')
                ->badge()
                ->color('info')
                ->getStateUsing(function ($record) {
                    return $this->topPartnersData->get($record->id)?->total_orders ?? 0;
                }),

            TextColumn::make('completed_orders')
                ->label('Đơn hoàn thành')
                ->badge()
                ->color('success')
                ->getStateUsing(function ($record) {
                    return $this->topPartnersData->get($record->id)?->completed_orders ?? 0;
                }),

            TextColumn::make('total_revenue')
                ->label('Tổng doanh thu')
                ->money('VND')
                ->color('primary')
                ->weight('bold')
                ->getStateUsing(function ($record) {
                    return $this->topPartnersData->get($record->id)?->total_revenue ?? 0;
                }),

            TextColumn::make('success_rate')
                ->label('Tỷ lệ hoàn thành')
                ->badge()
                ->color(fn($state): string => match (true) {
                    $state >= 90 => 'success',
                    $state >= 70 => 'warning',
                    default => 'danger',
                })
                ->getStateUsing(function ($record) {
                    $data = $this->topPartnersData->get($record->id);
                    if (!$data || $data->total_orders == 0) {
                        return 0;
                    }
                    return round(($data->completed_orders / $data->total_orders) * 100, 1);
                })
                ->formatStateUsing(fn($state) => $state . '%'),
        ];
    }
}
