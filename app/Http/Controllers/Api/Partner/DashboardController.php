<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\AppNotificationType;
use App\Enum\CacheKey;
use App\Enum\PartnerBillStatus;
use App\Enum\PartnerBillDetailStatus;
use App\Enum\StatisticType;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Statistical;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Settings\AppNotificationSettings;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * GET /api/partner/dashboard
     *
     * Response: { has_notification, statistical_data, popular_services, app_notification }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $appNotificationSettings = app(AppNotificationSettings::class);

        $avatarUrl = $user->getFirstMediaUrl('avatar', 'avatar_webp') ?: $user->avatar_url;

        $walletBalance = (int) $user->balanceInt;

        $revenue = Statistical::where('user_id', $user->id)->where('metrics_name', StatisticType::REVENUE_GENERATED->value)
            ->latest()
            ->first();
        $showData = $this->getShowData($user);

        $quarterlyRevenue = $this->getQuarterlyRevenue($user->id);

        return response()->json([
            'avatar_url' => $avatarUrl,
            'has_notification' => $user->unreadNotifications()->count() > 0,
            'wallet_balance' => $walletBalance,
            'revenue' => (int) $revenue?->metrics_value ?? 0,
            'show_data' => $showData,
            'recent_reviews_count' => 0,
            'recent_reviews_avatars' => [],
            'quarterly_revenue' => $quarterlyRevenue,
            'app_notification' => $this->formatPartnerNotificationSettings($appNotificationSettings),
        ]);
    }

    /**
     * @return array{type: string, notification_image: ?string, title: ?string, content: ?string, image: ?string}|null
     */
    private function formatPartnerNotificationSettings(AppNotificationSettings $settings): array|null
    {
        if (! $settings->partner_enabled) {
            return null;
        }

        if ($settings->partner_type === AppNotificationType::ImageOnly->value) {
            if (!$settings->partner_notification_image) {
                return null;
            }
            return [
                'type' => $settings->partner_type,
                'notification_image' => secure_asset($settings->partner_notification_image),
                'title' => null,
                'content' => null,
                'image' => null,
            ];
        }

        if ($settings->partner_type === AppNotificationType::TextAndImage->value) {
            return [
                'type' => $settings->partner_type,
                'notification_image' => null,
                'title' => $settings->partner_title,
                'content' => $settings->partner_content,
                'image' => secure_asset($settings->partner_notification_image),
            ];
        }

        if ($settings->partner_type === AppNotificationType::TextOnly->value) {
            return [
                'type' => $settings->partner_type,
                'notification_image' => null,
                'title' => $settings->partner_title,
                'content' => $settings->partner_content,
                'image' => null,
            ];
        }

        return null;
    }

    /**
     * Get count of new bills and bills waiting for confirmation
     *
     * @param User|null $user
     * @return array{new: int, waitingConfirmation: int}
     */
    private function getShowData($user): array
    {
        if (!$user) {
            return [
                'new' => '0',
                'waitingConfirmation' => '0',
            ];
        }

        $partnerServices =  Cache::tags([CacheKey::PARTNER_SERVICES->value])->rememberForever(CacheKey::PARTNER_SERVICES->value . "_dashboard_user_{$user->id}", function () use ($user) {
            return $user->partnerServices()
                ->where('status', 'approved')
                ->pluck('category_id')
                ->unique()
                ->toArray();
        });

        if (empty($partnerServices)) {
            return [
                'new' => '0',
                'waitingConfirmation' => '0',
            ];
        }

        $locationIds = $this->resolvePartnerServiceAreaIds($user);

        $newBillsQuery = PartnerBill::whereIn('category_id', $partnerServices)
            ->where('status', PartnerBillStatus::PENDING)
            ->whereDoesntHave('details', function ($query) use ($user) {
                $query->where('partner_id', $user->id);
            });

        if (! empty($locationIds)) {
            $newBillsQuery->whereIn('location_id', $locationIds);
        }

        $new = $newBillsQuery->count();

        $waitingConfirmation = PartnerBillDetail::wherePartnerId($user->id)->whereStatus(PartnerBillDetailStatus::NEW)->count();

        return [
            'new' => (string) $new,
            'waitingConfirmation' => (string) $waitingConfirmation,
        ];
    }

    /**
     * @return list<int>
     */
    private function resolvePartnerServiceAreaIds(User $user): array
    {
        return Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])
            ->rememberForever(CacheKey::PARTNER_SERVICE_AREAS->value . "_dashboard_user_{$user->id}", function () use ($user): array {
                return $user->partnerServiceAreas()
                    ->pluck('location_id')
                    ->map(fn($locationId): int => (int) $locationId)
                    ->unique()
                    ->values()
                    ->all();
            });
    }

    /**
     * Revenue by quarter for current year - TODO: add caching after testing
     * TODO: add caching after testing
     * Q1: Jan–Mar, Q2: Apr–Jun, Q3: Jul–Sep, Q4: Oct–Dec
     *
     * @return array<int, float> [Q1, Q2, Q3, Q4]
     */
    private function getQuarterlyRevenue(int $partnerId): array
    {
        $year = Carbon::now()->year;

        $revenueByMonth = PartnerBill::where('partner_id', $partnerId)
            ->where('status', PartnerBillStatus::COMPLETED)
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(final_total) as total_revenue')
            ->groupBy('month')
            ->pluck('total_revenue', 'month');

        $quarters = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [10, 11, 12],
        ];

        return array_map(
            fn(array $months) => (float) collect($months)->sum(fn($m) => $revenueByMonth->get($m, 0)),
            $quarters,
        );
    }
}
