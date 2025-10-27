<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PartnerBill;
use App\Models\FileProductBill;
use App\Enum\PartnerBillStatus;
use App\Enum\FileProductBillStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Biểu đồ doanh thu 12 tháng gần nhất';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected function getCacheKey(): string
    {
        return 'admin_revenue_chart_' . Carbon::now()->format('Y-m-d-H');
    }

    protected function getData(): array
    {
        // Cache dữ liệu trong 1 giờ
        return Cache::remember($this->getCacheKey(), 3600, function () {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();

            // Lấy doanh thu từ Partner Bills
            $partnerRevenue = PartnerBill::whereIn('status', [
                PartnerBillStatus::COMPLETED,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB
            ])
                ->where('created_at', '>=', $startDate)
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(final_total) as total_revenue')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function ($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Lấy doanh thu từ File Product Bills
            $fileProductRevenue = FileProductBill::where('status', FileProductBillStatus::PAID)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(final_total) as total_revenue')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function ($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Tạo dữ liệu cho 12 tháng
            $partnerMonthlyRevenue = [];
            $fileProductMonthlyRevenue = [];
            $totalMonthlyRevenue = [];
            $labels = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->format('M Y');
                $key = $date->format('Y-m');

                $partnerRev = $partnerRevenue->get($key)?->total_revenue ?? 0;
                $fileRev = $fileProductRevenue->get($key)?->total_revenue ?? 0;

                $partnerMonthlyRevenue[] = (float) $partnerRev;
                $fileProductMonthlyRevenue[] = (float) $fileRev;
                $totalMonthlyRevenue[] = (float) ($partnerRev + $fileRev);
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Dịch vụ (₫)',
                        'data' => $partnerMonthlyRevenue,
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Thiết kế (₫)',
                        'data' => $fileProductMonthlyRevenue,
                        'borderColor' => 'rgb(251, 146, 60)',
                        'backgroundColor' => 'rgba(251, 146, 60, 0.1)',
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Tổng (₫)',
                        'data' => $totalMonthlyRevenue,
                        'borderColor' => 'rgb(34, 197, 94)',
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'tension' => 0.4,
                        'borderWidth' => 3,
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
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat("vi-VN").format(context.parsed.y) + " ₫";
                            }
                            return label;
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return new Intl.NumberFormat("vi-VN", {
                                notation: "compact",
                                compactDisplay: "short"
                            }).format(value) + " ₫";
                        }',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
