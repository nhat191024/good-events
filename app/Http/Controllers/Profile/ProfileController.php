<?php

namespace App\Http\Controllers\Profile;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\User;
use App\Services\PartnerProfilePayload;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadMissing(['partnerProfile', 'media']);

        $isPartner = $user->partnerProfile && Partner::find($user->id)->hasRole(Role::PARTNER->value);

        $payload = $isPartner
            ? PartnerProfilePayload::for($user)
            : $this->clientPayload($user);
        
        return Inertia::render('profile/Profile', [
            'profile_type' => $isPartner ? 'partner' : 'client',
            'payload' => $payload,
        ]);
    }

    public function showJson(User $user)
    {
        $user->loadMissing(['partnerProfile', 'media']);

        if (! $user->hasRole(Role::PARTNER)) {
            abort(404);
        }

        return response()->json(PartnerProfilePayload::for($user));
    }

    private function clientPayload(User $inputUser): array
    {
        $user = Customer::with('media')->find($inputUser->id);
        $stats = $user->statistics()->get()->keyBy('metrics_name');

        $ordersPlaced = (int)(optional($stats->get('orders_placed'))->metrics_value ?? $user->partnerBillsAsClient()->count());
        $completedOrders = (int)(optional($stats->get('completed_orders'))->metrics_value ?? $user->partnerBillsAsClient()->where('status', 'completed')->count());
        $cancelPct = (string)(optional($stats->get('cancelled_orders_percentage'))->metrics_value
            ?? $this->calcCancelPct($user));

        $totalSpent = (float)(optional($stats->get('total_spent'))->metrics_value ?? $user->partnerBillsAsClient()->sum('final_total'));
        $avgRatingRaw = optional($stats->get('avg_rating'))->metrics_value ?? optional($stats->get('rating'))->metrics_value ?? null;
        $avgRating = is_numeric($avgRatingRaw) ? round((float) $avgRatingRaw, 1) : null;

        if ($avgRating === null) {
            $avgRatingFromReviews = $user->reviews()
                ->with('ratings')
                ->get()
                ->map(fn($review) => optional($review->ratings->firstWhere('key', 'overall'))->value)
                ->filter()
                ->avg();

            $avgRating = $avgRatingFromReviews ? round((float) $avgRatingFromReviews, 1) : null;
        }

        $recentBills = $user->partnerBillsAsClient()
            ->with(['event:id,name', 'category:id,name', 'partner:id,name'])
            ->latest('date')
            ->latest('created_at')
            ->take(3)
            ->get();

        $recentReviews = $user->authoredReviews()
            ->with(['ratings', 'reviewable' => function ($morph) {
                $morph->select('id', 'name');
            }])
            ->take(5)
            ->get();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'avatar_img_tag' => $user?->getAvatarImageTag(),
                'email' => $user->email,
                'phone' => $user->phone,
                'created_year' => optional($user->created_at)->format('Y'),
                'location' => '—',
                'partner_profile_name' => $user->partner_profile_name,
                'bio' => $user->bio,
                'email_verified_at' => optional($user->email_verified_at)->toIso8601String(),
                'is_verified' => (bool) $user->hasVerifiedEmail(),
            ],
            'stats' => [
                'orders_placed' => $ordersPlaced,
                'completed_orders' => $completedOrders,
                'cancelled_orders_pct' => $cancelPct,
                'total_spent' => $totalSpent,
                'last_active_human' => optional($user->updated_at)->diffForHumans(),
                'avg_rating' => $avgRating,
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
                'overall' => $r->ratings->firstWhere('key', 'overall')->value ?? null,
                'created_human' => $r->created_at?->diffForHumans(),
            ]),
            'intro' => $user->bio ?? null,
        ];
    }

    private function calcCancelPct(User $user): string
    {
        $total = $user->partnerBillsAsClient()->count();
        if ($total === 0) return '0%';
        $cancel = $user->partnerBillsAsClient()->where('status', 'cancelled')->count();
        return round($cancel * 100 / $total) . '%';
    }
}
