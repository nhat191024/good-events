<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\AppNotificationType;
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

        $recentReviews = $user->reviews()->where('created_at', '>=', Carbon::now()->subDay())->get();

        $recentReviewsCount = $recentReviews->count();
        $recentReviewsAvatars = $this->getRecentReviews($recentReviews);

        $quarterlyRevenue = $this->getQuarterlyRevenue($user->id);

        return response()->json([
            'avatar_url' => $avatarUrl,
            'has_notification' => $user->unreadNotifications()->count() > 0,
            'wallet_balance' => $walletBalance,
            'revenue' => (int) $revenue?->metrics_value ?? 0,
            'show_data' => $showData,
            'recent_reviews_count' => $recentReviewsCount,
            'recent_reviews_avatars' => $recentReviewsAvatars,
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
     * @param \App\Models\User|null $user
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

        //TODO: cache this forever and only refresh when partner add or update a service
        $partnerServices = $user->partnerServices()
            ->where('status', 'approved')
            ->pluck('category_id')
            ->unique()
            ->toArray();

        if (empty($partnerServices)) {
            return [
                'new' => '0',
                'waitingConfirmation' => '0',
            ];
        }

        $new = PartnerBill::whereIn('category_id', $partnerServices)
            ->where('status', PartnerBillStatus::PENDING)
            ->whereDoesntHave('details', function ($query) use ($user) {
                $query->where('partner_id', $user->id);
            })
            ->count();

        $waitingConfirmation = PartnerBillDetail::wherePartnerId($user->id)->whereStatus(PartnerBillDetailStatus::NEW)->count();

        return [
            'new' => (string) $new,
            'waitingConfirmation' => (string) $waitingConfirmation,
        ];
    }

    private function getRecentReviews($reviews)
    {
        $reviewerIds = $reviews->pluck('user_id')->unique()->take(4);
        $reviewersAvatars = User::whereIn('id', $reviewerIds)->pluck('avatar', 'id');

        return $reviewersAvatars;
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
