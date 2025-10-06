<?php

namespace App\Filament\Partner\Widgets;

use App\Enum\StatisticType;
use App\Models\Statistical;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PartnerStatisticsWidget extends StatsOverviewWidget
{
    // Giảm tần suất polling xuống 30s thay vì 2s mặc định
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // Cache thống kê trong 10 phút
        $statistics = Cache::remember("partner_stats_{$user->id}", 600, function () use ($user) {
            return Statistical::where('user_id', $user->id)->get()->keyBy('metrics_name');
        });

        return [
            // Số khách hàng
            Stat::make(__('statistics.number_of_customers'), $statistics->get(StatisticType::NUMBER_CUSTOMER->value)?->metrics_value ?? 0)
                ->description(__('statistics.total_customers_served'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            // Tỷ lệ hài lòng
            Stat::make(__('statistics.satisfaction_rate'), $this->formatSatisfactionRate($statistics->get(StatisticType::SATISFACTION_RATE->value)?->metrics_value ?? 0))
                ->description(__('statistics.average_customer_rating'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            // Doanh thu tạo ra
            Stat::make(__('statistics.revenue'), $this->formatCurrency($statistics->get(StatisticType::REVENUE_GENERATED->value)?->metrics_value ?? 0))
                ->description(__('statistics.total_revenue_generated'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            // Đơn hàng đã đặt
            Stat::make(__('statistics.orders_placed'), $statistics->get(StatisticType::ORDERS_PLACED->value)?->metrics_value ?? 0)
                ->description(__('statistics.total_orders_received'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            // Đơn hàng hoàn thành
            Stat::make(__('statistics.completed_orders'), $statistics->get(StatisticType::COMPLETED_ORDERS->value)?->metrics_value ?? 0)
                ->description(__('statistics.number_of_completed_orders'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // Tỷ lệ hủy đơn
            Stat::make(__('statistics.cancellation_rate'), $this->formatPercentage($statistics->get(StatisticType::CANCELLED_ORDERS_PERCENTAGE->value)?->metrics_value ?? 0))
                ->description(__('statistics.percentage_cancelled_orders'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }

    /**
     * Format satisfaction rate as percentage with star icon
     */
    private function formatSatisfactionRate(string|int|float $value): string
    {
        $rate = (float) $value;
        if ($rate <= 5) {
            // If it's already on a 5-point scale, convert to percentage
            return number_format(($rate / 5) * 100, 1) . '%';
        }
        // If it's already a percentage
        return number_format($rate, 1) . '%';
    }

    /**
     * Format currency value
     */
    private function formatCurrency(string|int|float $value): string
    {
        return number_format((float) $value, 0, ',', '.') . ' ₫';
    }

    /**
     * Format percentage value
     */
    private function formatPercentage(string|int|float $value): string
    {
        return number_format((float) $value, 1) . '%';
    }
}
