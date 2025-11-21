<?php

namespace App\Filament\Admin\Widgets;

use Carbon\Carbon;

use App\Models\PartnerBill;
use App\Models\FileProductBill;

use App\Enum\PartnerBillStatus;
use App\Enum\FileProductBillStatus;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Schemas\Schema;

use Illuminate\Support\Facades\Cache;

use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class AdminRevenueChart extends ChartWidget
{
    use HasFiltersSchema;

    protected ?string $heading = 'Biểu đồ doanh thu';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected static bool $isLazy = false;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DateRangePicker::make('date_range')
                ->label('Khoảng thời gian')
                ->placeholder('Chọn khoảng thời gian')
                ->displayFormat('DD/MM/YYYY')
                ->format('d/m/Y')
                // ->startDate(now()->subMonths(11)->startOfMonth())
                // ->endDate(now())
                ->maxDate(now())
                ->ranges([
                    'Hôm nay' => [now(), now()],
                    'Hôm qua' => [now()->subDay(), now()->subDay()],
                    '7 ngày qua' => [now()->subDays(6), now()],
                    '30 ngày qua' => [now()->subDays(29), now()],
                    'Tháng này' => [now()->startOfMonth(), now()],
                    'Tháng trước' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
                    '3 tháng qua' => [now()->subMonths(2)->startOfMonth(), now()],
                    '6 tháng qua' => [now()->subMonths(5)->startOfMonth(), now()],
                    '12 tháng qua' => [now()->subMonths(11)->startOfMonth(), now()],
                    'Năm nay' => [now()->startOfYear(), now()],
                    'Năm trước' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
                ])
                ->useRangeLabels()
                ->autoApply()
                ->firstDayOfWeek(1),
        ]);
    }

    protected function getCacheKey(): string
    {
        $dateRange = $this->filters['date_range'] ?? '';
        return 'admin_revenue_chart_' . md5($dateRange) . '_' . Carbon::now()->format('Y-m-d-H');
    }

    protected function getDateRange(): array
    {
        $dateRange = $this->filters['date_range'] ?? null;

        if ($dateRange) {
            // Parse date range string (format: "DD/MM/YYYY - DD/MM/YYYY")
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();

                // Xác định interval dựa trên khoảng cách
                $diffInDays = $startDate->diffInDays($endDate);
                $interval = match (true) {
                    $diffInDays <= 2 => 'hour',
                    $diffInDays <= 90 => 'day',
                    default => 'month',
                };

                return [
                    'start' => $startDate,
                    'end' => $endDate,
                    'interval' => $interval,
                ];
            }
        }

        // Default: 12 months
        return [
            'start' => Carbon::now()->subMonths(11)->startOfMonth(),
            'end' => Carbon::now(),
            'interval' => 'month',
        ];
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
