<?php

namespace App\Services;

use App\Models\User;
use App\Models\PartnerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PartnerProfilePayload
{
    public static function for(User $user): array
    {
        $latestReviewUpdated = optional(
            $user->reviews()->latest('updated_at')->first()
        )->updated_at?->timestamp ?? 0;

        // bump cache when reviews change so fresh feedback is returned
        $key = "profile:partner:v3:{$user->id}:r{$latestReviewUpdated}";

        return Cache::remember($key, now()->addDay(), function () use ($user) {
            $stats = $user->statistics()->get()->keyBy('metrics_name');

            $calcCancelPct = function (User $u): string {
                $total = $u->partnerBillsAsPartner()->count();
                if ($total === 0)
                    return '0%';
                $completed = $u->partnerBillsAsPartner()->where('status', 'paid')->count();
                $cancelled = $total - $completed;
                return round($cancelled * 100 / $total) . '%';
            };

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'location' => $user->location ?? 'nonne',
                    'joined_year' => optional($user->created_at)->format('Y'),
                    'is_pro' => true,
                    'rating' => (float) (optional($stats->get('rating'))->metrics_value ?? 5),
                    'total_reviews' => (int) (optional($stats->get('total_reviews'))->metrics_value ?? 0),
                    'total_customers' => (int) (optional($stats->get('number_customer'))->metrics_value ?? null),
                    'bio' => $user->bio,
                    'email_verified_at' => optional($user->email_verified_at)->toIso8601String(),
                    'is_verified' => $user->hasVerifiedEmail(),
                ],
                'stats' => [
                    'customers' => (int) (optional($stats->get('number_customer'))->metrics_value ?? 0),
                    'years' => now()->year - (int) optional($user->created_at)->format('Y'),
                    'satisfaction_pct' => (optional($stats->get('satisfaction_rate'))->metrics_value ?? '—') . "%",
                    'avg_response' => '14h',
                ],
                'quick' => [
                    'orders_placed' => (int) (optional($stats->get('orders_placed'))->metrics_value ?? $user->partnerBillsAsPartner()->count()),
                    'completed_orders' => (int) (optional($stats->get('completed_orders'))->metrics_value ?? $user->partnerBillsAsPartner()->where('status', 'paid')->count()),
                    'cancelled_orders_pct' => (string) (optional($stats->get('cancelled_orders_percentage'))->metrics_value ?? $calcCancelPct($user)),
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
                    ->with(['category:id,name'])
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
                    $authors = \App\Models\User::whereIn('id', $authorIds)->pluck('name', 'id');

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
            ];
        });
    }
}
