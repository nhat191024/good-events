<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\StatisticType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\PartnerCategory;
use App\Models\Statistical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/partner/dashboard
     *
     * Response: { has_notification, statistical_data, popular_services }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->loadMissing('partnerProfile');
        $stats = Statistical::where('user_id', $user->id)->get()->keyBy('metrics_name');

        $statisticalData = [
            StatisticType::NUMBER_CUSTOMER->value => (int) ($stats->get(StatisticType::NUMBER_CUSTOMER->value)?->metrics_value ?? 0),
            StatisticType::SATISFACTION_RATE->value => (float) ($stats->get(StatisticType::SATISFACTION_RATE->value)?->metrics_value ?? 0),
            StatisticType::REVENUE_GENERATED->value => (float) ($stats->get(StatisticType::REVENUE_GENERATED->value)?->metrics_value ?? 0),
            StatisticType::ORDERS_PLACED->value => (int) ($stats->get(StatisticType::ORDERS_PLACED->value)?->metrics_value ?? 0),
            StatisticType::COMPLETED_ORDERS->value => (int) ($stats->get(StatisticType::COMPLETED_ORDERS->value)?->metrics_value ?? 0),
            StatisticType::CANCELLED_ORDERS_PERCENTAGE->value => (float) ($stats->get(StatisticType::CANCELLED_ORDERS_PERCENTAGE->value)?->metrics_value ?? 0),
        ];

        $popularCategories = DB::table('partner_bills')
            ->select([
                'category_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('MAX(created_at) as latest_order'),
            ])
            ->where('partner_id', $user->id)
            ->where('status', '=', 'completed')
            ->groupBy('category_id')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get()
            ->keyBy('category_id');

        $categoryIds = $popularCategories->keys()->map(fn($id) => (int) $id)->all();
        $categories = $categoryIds
            ? PartnerCategory::query()
            ->whereIn('id', $categoryIds)
            ->with('media')
            ->get()
            ->keyBy('id')
            : collect();

        $popularServices = collect($popularCategories)->map(function ($statsRow, $categoryId) use ($categories) {
            $category = $categories->get((int) $categoryId);

            return [
                'category_id' => (int) $categoryId,
                'name' => $category?->name,
                'slug' => $category?->slug,
                'image' => $category?->getFirstMediaUrl('images', 'thumb'),
                'order_count' => (int) $statsRow->order_count,
                'total_revenue' => (float) $statsRow->total_revenue,
                'latest_order' => $statsRow->latest_order,
            ];
        })->values();

        return response()->json([
            'has_notification' => $user->unreadNotifications()->count() > 0,
            'statistical_data' => $statisticalData,
            'popular_services' => $popularServices,
        ]);
    }
}
