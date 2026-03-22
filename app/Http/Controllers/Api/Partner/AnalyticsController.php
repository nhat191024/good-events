<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\CacheKey;
use App\Enum\PartnerBillStatus;
use App\Enum\StatisticType;
use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use App\Models\Statistical;
use App\Models\PartnerBill;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * GET /api/partner/analytics/statistics
     *
     * Returns partner statistics (mirrors PartnerStatisticsWidget).
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $statistics = Cache::remember(CacheKey::PARTNER_STATS->value . $user->id, 600, function () use ($user) {
            return Statistical::where('user_id', $user->id)->get()->keyBy('metrics_name');
        });

        $satisfactionRaw = (float) ($statistics->get(StatisticType::SATISFACTION_RATE->value)?->metrics_value ?? 0);

        return response()->json([
            'number_of_customers' => (int) ($statistics->get(StatisticType::NUMBER_CUSTOMER->value)?->metrics_value ?? 0),
            'satisfaction_rate' => $satisfactionRaw <= 5
                ? round(($satisfactionRaw / 5) * 100, 1)
                : round($satisfactionRaw, 1),
            'revenue' => (float) ($statistics->get(StatisticType::REVENUE_GENERATED->value)?->metrics_value ?? 0),
            'orders_placed' => (int) ($statistics->get(StatisticType::ORDERS_PLACED->value)?->metrics_value ?? 0),
            'completed_orders' => (int) ($statistics->get(StatisticType::COMPLETED_ORDERS->value)?->metrics_value ?? 0),
            'cancellation_rate' => round((float) ($statistics->get(StatisticType::CANCELLED_ORDERS_PERCENTAGE->value)?->metrics_value ?? 0), 1),
        ]);
    }

    /**
     * GET /api/partner/analytics/revenue-chart
     *
     * Returns monthly revenue for the last 12 months (mirrors PartnerRevenueChart).
     */
    public function revenueChart(Request $request): JsonResponse
    {
        $user = $request->user();

        $cacheKey = CacheKey::PARTNER_REVENUE_CHART->value . $user->id . '_' . Carbon::now()->format('Y-m-d-H');

        $months = Cache::remember($cacheKey, 3600, function () use ($user) {
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();

            $revenueData = PartnerBill::where('partner_id', $user->id)
                ->where('status', PartnerBillStatus::COMPLETED)
                ->where('created_at', '>=', $startDate)
                ->get(['created_at', 'final_total'])
                ->groupBy(fn($bill) => $bill->created_at->format('Y-m'))
                ->map(fn($group) => $group->sum('final_total'));

            $months = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $key = $date->format('Y-m');

                $months[] = [
                    'month' => $key,
                    'label' => $date->format('M Y'),
                    'revenue' => (float) ($revenueData->get($key) ?? 0),
                ];
            }

            return $months;
        });

        return response()->json(['data' => $months]);
    }

    /**
     * GET /api/partner/analytics/top-services
     *
     * Returns top 5 most ordered service categories (mirrors PartnerTopServicesWidget).
     */
    public function topServices(Request $request): JsonResponse
    {
        $user = $request->user();

        $categories = Cache::remember(CacheKey::PARTNER_TOP_SERVICES->value . $user->id, 600, function () use ($user) {
            return $this->resolveTopServices($user->id);
        });

        return response()->json(['data' => $categories]);
    }

    private function resolveTopServices(int $userId): \Illuminate\Support\Collection
    {
        $stats = DB::table('partner_bills')
            ->select([
                'category_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('MAX(created_at) as latest_order'),
            ])
            ->where('partner_id', $userId)
            ->where('status', PartnerBillStatus::COMPLETED->value)
            ->groupBy('category_id')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get()
            ->keyBy('category_id');

        if ($stats->isEmpty()) {
            return collect();
        }

        $categoryIds = array_map('intval', $stats->pluck('category_id')->toArray());

        return PartnerCategory::whereIn('id', $categoryIds)
            ->get()
            ->map(function ($category) use ($stats) {
                $stat = $stats->get($category->id);

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'order_count' => (int) $stat->order_count,
                    'total_revenue' => (float) $stat->total_revenue,
                    'latest_order' => $stat->latest_order,
                ];
            })
            ->sortByDesc('order_count')
            ->values();
    }
}
