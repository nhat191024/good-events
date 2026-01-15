<?php

namespace App\Services;

use App\Models\User;
use App\Models\PartnerService;
use App\Enum\PartnerBillStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PartnerProfilePayload
{
    public static function for(User $user): array
    {
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

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'location' => $user->location,
                'joined_year' => optional($user->created_at)->format('Y'),
                'is_pro' => true,
                'rating' => (float) (optional($stats->get('rating'))->metrics_value ?? 5),
                'total_reviews' => (int) (optional($stats->get('total_reviews'))->metrics_value ?? 0),
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

                            if (method_exists($m, 'getTemporaryUrl')) {
                                try {
                                    $url = $m->getTemporaryUrl(now()->addMinutes(10));
                                } catch (\Throwable $e) {
                                    Log::warning('PartnerProfilePayload: failed to build temporary URL', [
                                        'service_id' => $s->id,
                                        'media_id' => $m->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                    $url = $m->getFullUrl();
                                }
                            }

                            return [
                                'id' => $m->id,
                                'url' => $url,
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
                $reviews = $user->reviews()
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
