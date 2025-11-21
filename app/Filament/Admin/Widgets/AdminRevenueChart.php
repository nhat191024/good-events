<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PartnerBill;
use App\Models\FileProductBill;
use App\Enum\PartnerBillStatus;
use App\Enum\FileProductBillStatus;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class AdminRevenueChart extends ChartWidget
{
    use HasFiltersSchema;

    protected ?string $heading = 'Biểu đồ doanh thu 12 tháng gần nhất';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected static bool $isLazy = false;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period')
                ->label('Khoảng thời gian')
                ->options([
                    'today' => 'Hôm nay',
                    'yesterday' => 'Hôm qua',
                    'last_7_days' => '7 ngày qua',
                    'last_30_days' => '30 ngày qua',
                    'this_month' => 'Tháng này',
                    'last_month' => 'Tháng trước',
                    'last_3_months' => '3 tháng qua',
                    'last_6_months' => '6 tháng qua',
                    'last_12_months' => '12 tháng qua',
                    'this_year' => 'Năm nay',
                    'last_year' => 'Năm trước',
                ])
                ->default('last_12_months')
                ->native(false)
                ->selectablePlaceholder(false),
        ]);
    }

    protected function getCacheKey(): string
    {
        $period = $this->filters['period'] ?? 'last_12_months';
        return 'admin_revenue_chart_' . $period . '_' . Carbon::now()->format('Y-m-d-H');
    }

    protected function getDateRange(): array
    {
        $period = $this->filters['period'] ?? 'last_12_months';
        return match ($period) {
            'today' => [
                'start' => Carbon::today(),
                'end' => Carbon::now(),
                'interval' => 'hour',
            ],
            'yesterday' => [
                'start' => Carbon::yesterday()->startOfDay(),
                'end' => Carbon::yesterday()->endOfDay(),
                'interval' => 'hour',
            ],
            'last_7_days' => [
                'start' => Carbon::now()->subDays(6)->startOfDay(),
                'end' => Carbon::now(),
                'interval' => 'day',
            ],
            'last_30_days' => [
                'start' => Carbon::now()->subDays(29)->startOfDay(),
                'end' => Carbon::now(),
                'interval' => 'day',
            ],
            'this_month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now(),
                'interval' => 'day',
            ],
            'last_month' => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth(),
                'interval' => 'day',
            ],
            'last_3_months' => [
                'start' => Carbon::now()->subMonths(2)->startOfMonth(),
                'end' => Carbon::now(),
                'interval' => 'month',
            ],
            'last_6_months' => [
                'start' => Carbon::now()->subMonths(5)->startOfMonth(),
                'end' => Carbon::now(),
                'interval' => 'month',
            ],
            'last_12_months' => [
                'start' => Carbon::now()->subMonths(11)->startOfMonth(),
                'end' => Carbon::now(),
                'interval' => 'month',
            ],
            'this_year' => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now(),
                'interval' => 'month',
            ],
            'last_year' => [
                'start' => Carbon::now()->subYear()->startOfYear(),
                'end' => Carbon::now()->subYear()->endOfYear(),
                'interval' => 'month',
            ],
            default => [
                'start' => Carbon::now()->subMonths(11)->startOfMonth(),
                'end' => Carbon::now(),
                'interval' => 'month',
            ],
        };
    }

    protected function getData(): array
    {
        // Cache dữ liệu trong 1 giờ
        return Cache::remember($this->getCacheKey(), 3600, function () {
            $dateRange = $this->getDateRange();
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $interval = $dateRange['interval'];

            // Xác định format query dựa trên interval
            $groupByFormat = match ($interval) {
                'hour' => 'YEAR(created_at) as year, MONTH(created_at) as month, DAY(created_at) as day, HOUR(created_at) as hour, SUM(final_total) as total_revenue',
                'day' => 'YEAR(created_at) as year, MONTH(created_at) as month, DAY(created_at) as day, SUM(final_total) as total_revenue',
                'month' => 'YEAR(created_at) as year, MONTH(created_at) as month, SUM(final_total) as total_revenue',
            };

            $groupByFields = match ($interval) {
                'hour' => ['year', 'month', 'day', 'hour'],
                'day' => ['year', 'month', 'day'],
                'month' => ['year', 'month'],
            };

            // Lấy doanh thu từ Partner Bills
            $partnerRevenue = PartnerBill::whereIn('status', [
                PartnerBillStatus::COMPLETED,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB
            ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw($groupByFormat)
                ->groupBy($groupByFields)
                ->orderBy('year')
                ->orderBy('month')
                ->when($interval !== 'month', fn($q) => $q->orderBy('day'))
                ->when($interval === 'hour', fn($q) => $q->orderBy('hour'))
                ->get()
                ->keyBy(function ($item) use ($interval) {
                    return match ($interval) {
                        'hour' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->day, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->hour, 2, '0', STR_PAD_LEFT),
                        'day' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->day, 2, '0', STR_PAD_LEFT),
                        'month' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    };
                });

            // Lấy doanh thu từ File Product Bills
            $fileProductRevenue = FileProductBill::where('status', FileProductBillStatus::PAID)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw($groupByFormat)
                ->groupBy($groupByFields)
                ->orderBy('year')
                ->orderBy('month')
                ->when($interval !== 'month', fn($q) => $q->orderBy('day'))
                ->when($interval === 'hour', fn($q) => $q->orderBy('hour'))
                ->get()
                ->keyBy(function ($item) use ($interval) {
                    return match ($interval) {
                        'hour' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->day, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->hour, 2, '0', STR_PAD_LEFT),
                        'day' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->day, 2, '0', STR_PAD_LEFT),
                        'month' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    };
                });

            // Tạo dữ liệu theo khoảng thời gian
            $partnerMonthlyRevenue = [];
            $fileProductMonthlyRevenue = [];
            $totalMonthlyRevenue = [];
            $labels = [];

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $key = match ($interval) {
                    'hour' => $current->format('Y-m-d-H'),
                    'day' => $current->format('Y-m-d'),
                    'month' => $current->format('Y-m'),
                };

                $label = match ($interval) {
                    'hour' => $current->format('H:i'),
                    'day' => $current->format('d/m'),
                    'month' => $current->format('M Y'),
                };

                $labels[] = $label;

                $partnerRev = $partnerRevenue->get($key)?->total_revenue ?? 0;
                $fileRev = $fileProductRevenue->get($key)?->total_revenue ?? 0;

                $partnerMonthlyRevenue[] = (float) $partnerRev;
                $fileProductMonthlyRevenue[] = (float) $fileRev;
                $totalMonthlyRevenue[] = (float) ($partnerRev + $fileRev);

                $current = match ($interval) {
                    'hour' => $current->addHour(),
                    'day' => $current->addDay(),
                    'month' => $current->addMonth(),
                };
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
