<?php

namespace App\Filament\Partner\Widgets;

use App\Models\PartnerBill;
use App\Enum\PartnerBillStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PartnerRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Biểu đồ doanh thu theo tháng';

    // Giảm tần suất polling của widget (mặc định là 2s, tăng lên 30s)
    protected int | string | array $columnSpan = 'full';

    // Cache key cho dữ liệu
    protected function getCacheKey(): string
    {
        return 'partner_revenue_chart_' . Auth::id() . '_' . Carbon::now()->format('Y-m-d-H');
    }

    protected function getData(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Cache dữ liệu trong 1 giờ để tránh query liên tục
        return Cache::remember($this->getCacheKey(), 3600, function () use ($user) {
            // Tính ngày bắt đầu (12 tháng trước)
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();

            // Lấy tất cả dữ liệu doanh thu trong 12 tháng bằng 1 query duy nhất
            $revenueData = PartnerBill::where('partner_id', $user->id)
                ->where('status', PartnerBillStatus::COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(final_total) as total_revenue')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function ($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Tạo labels và data cho 12 tháng
            $monthlyRevenue = [];
            $labels = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');

                $key = $date->format('Y-m');
                $revenue = $revenueData->get($key)?->total_revenue ?? 0;
                $monthlyRevenue[] = (float) $revenue;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Doanh thu (₫)',
                        'data' => $monthlyRevenue,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.4,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return new Intl.NumberFormat("vi-VN").format(value) + " ₫"; }',
                    ],
                ],
            ],
        ];
    }

    /**
     * Clear cache khi có dữ liệu mới - có thể gọi từ model events
     */
    public static function clearCache(int $partnerId): void
    {
        $cacheKey = 'partner_revenue_chart_' . $partnerId . '_' . Carbon::now()->format('Y-m-d-H');
        Cache::forget($cacheKey);
    }
}
