<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PartnerProfilePayload
{
    public static function for(User $user): array
    {
        $key = "profile:partner:{$user->id}";

        return Cache::remember($key, now()->addDay(), function () use ($user) {
            
            $stats = $user->statistics()->get()->keyBy('metrics_name');

            $calcCancelPct = function (User $u): string {
                $total = $u->partnerBillsAsPartner()->count();
                if ($total === 0) return '0%';
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
                    'rating' => (float)($stats['rating']->metrics_value ?? 5),
                    'total_reviews' => (int)($stats['total_reviews']->metrics_value ?? 0),
                    'total_customers' => (int)($stats['number_customer']->metrics_value ?? null),
                ],
                'stats' => [
                    'customers' => (int)($stats['number_customer']->metrics_value ?? 0),
                    'years' => now()->year - (int)optional($user->created_at)->format('Y'),
                    'satisfaction_pct' => ($stats['satisfaction_rate']->metrics_value ?? '—') . "%",
                    'avg_response' => '14h', 
                ],
                'quick' => [
                    'orders_placed' => (int)($stats['orders_placed']->metrics_value ?? $user->partnerBillsAsPartner()->count()),
                    'completed_orders' => (int)($stats['completed_orders']->metrics_value ?? $user->partnerBillsAsPartner()->where('status', 'paid')->count()),
                    'cancelled_orders_pct' => (string)($stats['cancelled_orders_percentage']->metrics_value ?? $calcCancelPct($user)),
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
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->category?->name,
                        'field' => $s->category?->name,
                        'price' => null,
                    ])
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
                        return [
                            'id' => $r->id,
                            'author' => $authors[$r->user_id] ?? '—',
                            'rating' => optional($r->ratings->firstWhere('key', 'overall'))->value,
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
