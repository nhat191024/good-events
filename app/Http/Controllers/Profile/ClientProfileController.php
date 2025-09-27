<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PartnerBill;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ClientProfileController extends Controller
{
    public function show(User $user)
    {
        // cache key theo user
        $key = "profile:client:{$user->id}";

        $payload = Cache::remember($key, now()->addMinutes(10), function () use ($user) {
            // --- Quick stats (ưu tiên statistics table, fallback tính nhanh) ---
            $stats = $user->statistics()->get()->keyBy('metrics_name');

            $ordersPlaced = (int)($stats['orders_placed']->metrics_value ?? $user->partnerBillsAsClient()->count());
            $completedOrders = (int)($stats['completed_orders']->metrics_value ?? $user->partnerBillsAsClient()->where('status','completed')->count());
            $cancelPct = (string)($stats['cancelled_orders_percentage']->metrics_value
                            ?? $this->calcCancelPct($user));

            $totalSpent = (float)($stats['total_spent']->metrics_value ?? $user->partnerBillsAsClient()->sum('final_total'));

            // --- Recent orders ---
            $recentBills = $user->partnerBillsAsClient()
                ->with(['event:id,name','category:id,name','partner:id,name'])
                ->latest('date')
                ->latest('created_at')
                ->take(3)
                ->get();

            // --- Recent public reviews written by this client ---
            $recentReviews = $user->authoredReviews()
                ->with(['ratings','reviewable' => function ($morph) {
                    // có thể review User (partner) hoặc PartnerCategory, v.v.
                    $morph->select('id','name');
                }])
                ->take(5)
                ->get();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url, // accessor
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'created_year' => optional($user->created_at)->format('Y'),
                    'location' => '—', // chưa có bảng địa lý cho user -> để placeholder
                ],
                'stats' => [
                    'orders_placed' => $ordersPlaced,
                    'completed_orders' => $completedOrders,
                    'cancelled_orders_pct' => $cancelPct, // "9%" như ảnh
                    'total_spent' => $totalSpent,
                    'last_active_human' => optional($user->updated_at)->diffForHumans(), // ví dụ "10 phút trước"
                ],
                'recent_bills' => $recentBills->map(fn($b) => [
                    'id' => $b->id,
                    'code' => $b->code,
                    'status' => $b->status,
                    'final_total' => $b->final_total,
                    'date' => $b->date?->toDateString(),
                    'event' => $b->event?->name,
                    'category' => $b->category?->name,
                    'partner_name' => $b->partner?->name,
                ]),
                'recent_reviews' => $recentReviews->map(fn($r) => [
                    'id' => $r->id,
                    'subject_name' => $r->reviewable?->name ?? '—',
                    'department' => $r->department,
                    'review' => $r->review,
                    'overall' => $r->ratings->firstWhere('key','overall')->value ?? null,
                    'created_human' => $r->created_at?->diffForHumans(),
                ]),
                'intro' => $stats['bio']->metrics_value ?? null, // nếu có
            ];
        });

        return Inertia::render('profile/Client', $payload);
    }

    private function calcCancelPct(User $user): string
    {
        $total = $user->partnerBillsAsClient()->count();
        if ($total === 0) return '0%';
        $cancel = $user->partnerBillsAsClient()->where('status','cancelled')->count();
        return round($cancel * 100 / $total) . '%';
    }
}
