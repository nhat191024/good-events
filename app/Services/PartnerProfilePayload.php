<?php

namespace App\Services;

use App\Models\PartnerService;
use App\Enum\PartnerBillStatus;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PartnerProfilePayload
{
    public static function for(User $inputUser): array
    {
        $user = Partner::where('id', $inputUser->id)
            ->with('media')
            ->firstOrFail();

        if ($inputUser->relationLoaded('partnerProfile')) {
            $user->setRelation('partnerProfile', $inputUser->getRelation('partnerProfile'));
        } else {
            $user->loadMissing('partnerProfile');
        }

        $stats = $user->statistics()->get()->keyBy('metrics_name');

        $ordersPlacedStat = optional($stats->get('orders_placed'))->metrics_value;
        $completedOrdersStat = optional($stats->get('completed_orders'))->metrics_value;
        $cancelledOrdersPctStat = optional($stats->get('cancelled_orders_percentage'))->metrics_value;

        $realTotal = null;
        $realCompleted = null;

        $getTotal = function () use ($user, &$realTotal) {
            return $realTotal ??= $user->partnerBillsAsPartner()->count();
        };

        $getCompleted = function () use ($user, &$realCompleted) {
            return $realCompleted ??= $user->partnerBillsAsPartner()->where('status', PartnerBillStatus::COMPLETED)->count();
        };

        $ordersPlaced = (int) ($ordersPlacedStat ?? $getTotal());
        $completedOrders = (int) ($completedOrdersStat ?? $getCompleted());

        if ($cancelledOrdersPctStat !== null) {
            $cancelledOrdersPct = (string) $cancelledOrdersPctStat;
        } else {
            $total = $getTotal();
            if ($total === 0) {
                $cancelledOrdersPct = '0%';
            } else {
                $completed = $getCompleted();
                $cancelled = $total - $completed;
                $cancelledOrdersPct = round($cancelled * 100 / $total) . '%';
            }
        }

        // Stats may be stored under different key names depending on the job that produced them
        $ratingStat = optional($stats->get('rating'))->metrics_value
            ?? optional($stats->get('average_stars'))->metrics_value;
        $totalReviewsStat = optional($stats->get('total_reviews'))->metrics_value
            ?? optional($stats->get('total_ratings'))->metrics_value;

        if ($ratingStat === null || $totalReviewsStat === null) {
            $userModel = Partner::find($user->id);
            $allReviews = $userModel->reviews()->with('ratings')->get();
            $dynamicTotalReviews = $allReviews->count();

            $avgRatingFromReviews = $allReviews
                ->map(fn($review) => optional($review->ratings->firstWhere('key', 'rating'))->value
                    ?? optional($review->ratings->firstWhere('key', 'overall'))->value)
                ->filter()
                ->avg();

            $dynamicRating = $avgRatingFromReviews ? round((float) $avgRatingFromReviews, 1) : 5.0;
        }

        $finalRating = (float) ($ratingStat ?? $dynamicRating ?? 5.0);
        $finalTotalReviews = (int) ($totalReviewsStat ?? $dynamicTotalReviews ?? 0);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'avatar_img_tag' => $user->getAvatarImageTag(),
                'location' => $user->location,
                'joined_year' => optional($user->created_at)->format('Y'),
                'is_pro' => true,
                'rating' => $finalRating,
                'total_reviews' => $finalTotalReviews,
                'total_customers' => (int) (optional($stats->get('number_customer'))->metrics_value ?? null),
                'bio' => $user->bio,
                'email_verified_at' => optional($user->email_verified_at)->toIso8601String(),
                'is_verified' => $user->hasVerifiedEmail(),
                'is_legit' => (bool) optional($user->partnerProfile)->is_legit,
            ],
            'stats' => [
                'customers' => (int) (optional($stats->get('number_customer'))->metrics_value ?? 0),
                'years' => $user->created_at ? now()->diffInYears($user->created_at) : 0,
                'satisfaction_pct' => (optional($stats->get('satisfaction_rate'))->metrics_value ?? '—') . "%",
                'avg_response' => '14h',
            ],
            'quick' => [
                'orders_placed' => $ordersPlaced,
                'completed_orders' => $completedOrders,
                'cancelled_orders_pct' => $cancelledOrdersPct,
                'last_active_human' => optional($user->updated_at)->diffForHumans(),
            ],
            'contact' => [
                'phone' => $user->phone,
                'email' => $user->email,
                'response_time' => 'Trong 1 giờ',
                'languages' => ['Tiếng Việt', 'English'],
                'timezone' => 'GMT+7',
            ],
            'services' => $user->partnerServices()
                ->with(['category:id,name', 'media'])
                ->select('id', 'category_id')
                ->get()
                ->map(function ($s) {
                    $mediaItems = $s->getMedia('service_images');

                    $media = $mediaItems
                        ->take(10)
                        ->map(function ($m) use ($s) {

                            $url = $m->getFullUrl();
                            $imageTag = $m->img('thumb')->attributes([
                                'class' => 'w-full h-full object-cover lazy-image',
                                'loading' => 'lazy',
                                'alt' => $s->category?->name,
                            ])->toHtml();

                            return [
                                'id' => $m->id,
                                'url' => $url,
                                'image_tag' => $imageTag,
                            ];
                        })
                        ->values()
                        ->all();

                    return [
                        'id' => $s->id,
                        'name' => $s->category?->name,
                        'field' => $s->category?->name,
                        'price' => null,
                        'media' => $media,
                    ];
                })
                ->values(),
            'reviews' => (function () use ($user) {
                $userModel = Partner::find($user->id);
                $reviews = $userModel->reviews()
                    ->with(['ratings'])
                    ->latest('created_at')
                    ->take(3)
                    ->get();

                $authorIds = $reviews->pluck('user_id')->filter()->unique();
                $authors = User::whereIn('id', $authorIds)->pluck('name', 'id');

                return $reviews->map(function ($r) use ($authors) {
                    $rating = optional($r->ratings->firstWhere('key', 'rating'))->value
                        ?? optional($r->ratings->firstWhere('key', 'overall'))->value;

                    return [
                        'id' => $r->id,
                        'author' => $authors[$r->user_id] ?? '—',
                        'rating' => $rating,
                        'review' => $r->review,
                        'created_human' => optional($r->created_at)->diffForHumans(),
                    ];
                });
            })(),
            'intro' => $user->bio ?? null,
            'video_url' => optional($user->partnerProfile)->video_url,
        ];
    }
}
