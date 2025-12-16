<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\PartnerBill;
use App\Models\FileProductBill;
use App\Enum\PartnerBillStatus;
use App\Enum\FileProductBillStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Settings\PartnerSettings;

class AdminStatisticsWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        // Cache thống kê trong 10 phút
        $stats = Cache::remember('admin_dashboard_stats', 600, function () {
            $partnerRevenue = $this->getPartnerBillRevenue();
            return [
                'total_revenue' => $this->getTotalRevenue(),
                'partner_bill_revenue' => $partnerRevenue,
                'file_product_revenue' => $this->getFileProductRevenue(),
                'profit_revenue' => $this->getProfitRevenue($partnerRevenue),
                'total_partners' => User::whereHas('partnerProfile')->count(),
                'total_clients' => User::whereDoesntHave('partnerProfile')->count(),
                'total_orders' => PartnerBill::count(),
                'completed_orders' => PartnerBill::where('status', PartnerBillStatus::COMPLETED)->count(),
                'file_product_sales' => FileProductBill::where('status', FileProductBillStatus::PAID)->count(),
            ];
        });

        return [
            // Tổng doanh thu
            Stat::make('Tổng doanh thu', $this->formatCurrency($stats['total_revenue']))
                ->description('Từ tất cả nguồn')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueSparkline()),

            // Doanh thu từ Partner Services
            Stat::make('Doanh thu dịch vụ', $this->formatCurrency($stats['partner_bill_revenue']))
                ->description('Từ thuê nhân sự & vật tư')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            // Doanh thu từ File Products
            Stat::make('Doanh thu thiết kế', $this->formatCurrency($stats['file_product_revenue']))
                ->description('Từ bán file assets')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            // Lợi nhuận
            Stat::make('Lợi nhuận', $this->formatCurrency($stats['profit_revenue']))
                ->description('Từ phí dịch vụ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // Tổng số Partners
            Stat::make('Tổng đối tác', $stats['total_partners'])
                ->description('Đang hoạt động')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            // Tổng số Clients
            Stat::make('Tổng khách hàng', $stats['total_clients'])
                ->description('Đã đăng ký')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('gray'),

            // Tổng đơn hàng
            Stat::make('Tổng đơn hàng', $stats['total_orders'])
                ->description($stats['completed_orders'] . ' đơn hoàn thành')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            // Tổng đơn hàng File Products
            Stat::make('Đơn thiết kế bán ra', $stats['file_product_sales'])
                ->description('Đơn đã thanh toán')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),
        ];
    }

    private function getTotalRevenue(): float
    {
        $partnerRevenue = PartnerBill::whereIn('status', [
            PartnerBillStatus::COMPLETED,
            PartnerBillStatus::CONFIRMED,
            PartnerBillStatus::IN_JOB
        ])->sum('final_total');

        $fileProductRevenue = FileProductBill::where('status', FileProductBillStatus::PAID)
            ->sum('final_total');

        return $partnerRevenue + $fileProductRevenue;
    }

    private function getPartnerBillRevenue(): float
    {
        return PartnerBill::whereIn('status', [
            PartnerBillStatus::COMPLETED,
            PartnerBillStatus::CONFIRMED,
            PartnerBillStatus::IN_JOB
        ])->sum('final_total');
    }

    private function getFileProductRevenue(): float
    {
        return FileProductBill::where('status', FileProductBillStatus::PAID)
            ->sum('final_total');
    }

    private function getProfitRevenue(float $partnerRevenue): float
    {
        $feePercentage = app(PartnerSettings::class)->fee_percentage;
        return $partnerRevenue * $feePercentage / 100;
    }

    private function getRevenueSparkline(): array
    {
        // Lấy doanh thu 7 ngày gần nhất cho sparkline
        $data = Cache::remember('admin_revenue_sparkline', 3600, function () {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');

                $partnerRevenue = PartnerBill::whereIn('status', [
                    PartnerBillStatus::COMPLETED,
                    PartnerBillStatus::CONFIRMED,
                    PartnerBillStatus::IN_JOB
                ])
                    ->whereDate('created_at', $date)
                    ->sum('final_total');

                $fileRevenue = FileProductBill::where('status', FileProductBillStatus::PAID)
                    ->whereDate('created_at', $date)
                    ->sum('final_total');

                $days[] = $partnerRevenue + $fileRevenue;
            }
            return $days;
        });

        return $data;
    }

    private function formatCurrency(float $value): string
    {
        return number_format($value, 0, ',', '.') . ' ₫';
    }
}
