<?php

namespace App\Http\Controllers\Api;

use App\Enum\PartnerBillStatus;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\Api\PartnerServiceResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->loadMissing('partnerProfile');

        return response()->json([
            'user' => $user ? new UserResource($user) : null,
            'must_verify_email' => $user instanceof MustVerifyEmail,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $user->loadMissing('partnerProfile');

        $isPartner = $user->partnerProfile && $user->hasRole(Role::PARTNER);

        $payload = $isPartner
            ? $this->partnerPayload($user)
            : $this->clientPayload($user);

        return response()->json([
            'profile_type' => $isPartner ? 'partner' : 'client',
            'payload' => $payload,
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->safe()->except('avatar');

        $user->fill($validated);

        if (array_key_exists('bio', $validated)) {
            $bio = trim((string) ($validated['bio'] ?? ''));
            $user->bio = $bio === '' ? null : $bio;
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = Str::ulid() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('uploads/avatars', $filename, 'public');

            if ($user->getOriginal('avatar') && !Str::startsWith($user->getOriginal('avatar'), ['http://', 'https://'])) {
                Storage::disk('public')->delete($user->getOriginal('avatar'));
            }

            $user->avatar = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        Cache::forget("profile:client:v2:{$user->id}");
        Cache::forget("profile:partner:v2:{$user->id}");

        $user->loadMissing('partnerProfile');
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()?->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    private function clientPayload(User $user): array
    {
        $stats = $user->statistics()->get()->keyBy('metrics_name');

        $ordersPlaced = (int) (optional($stats->get('orders_placed'))->metrics_value ?? $user->partnerBillsAsClient()->count());
        $completedOrders = (int) (optional($stats->get('completed_orders'))->metrics_value ?? $user->partnerBillsAsClient()->where('status', 'completed')->count());
        $cancelPct = (string) (optional($stats->get('cancelled_orders_percentage'))->metrics_value
            ?? $this->calcCancelPct($user));

        $totalSpent = (float) (optional($stats->get('total_spent'))->metrics_value ?? $user->partnerBillsAsClient()->sum('final_total'));
        $avgRatingRaw = optional($stats->get('avg_rating'))->metrics_value ?? optional($stats->get('rating'))->metrics_value ?? null;
        $avgRating = is_numeric($avgRatingRaw) ? round((float) $avgRatingRaw, 1) : null;

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
                'email' => $user->email,
                'phone' => $user->phone,
                'created_year' => optional($user->created_at)->format('Y'),
                'location' => null,
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
            'recent_bills' => $recentBills->map(fn ($b) => [
                'id' => $b->id,
                'code' => $b->code,
                'status' => $b->status,
                'final_total' => $b->final_total,
                'date' => $b->date?->toDateString(),
                'event' => $b->event?->name,
                'category' => $b->category?->name,
                'partner_name' => $b->partner?->name,
            ]),
            'recent_reviews' => $recentReviews->map(fn ($r) => [
                'id' => $r->id,
                'subject_name' => $r->reviewable?->name ?? null,
                'department' => $r->department,
                'review' => $r->review,
                'overall' => $r->ratings->firstWhere('key', 'overall')->value ?? null,
                'created_human' => $r->created_at?->diffForHumans(),
            ]),
            'intro' => $user->bio ?? null,
        ];
    }

    private function partnerPayload(User $user): array
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
            return $realCompleted ??= $user->partnerBillsAsPartner()
                ->where('status', PartnerBillStatus::COMPLETED)
                ->count();
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

        $services = $user->partnerServices()
            ->with(['category:id,name', 'media'])
            ->select('id', 'category_id', 'user_id', 'status')
            ->get();

        $reviews = $user->reviews()
            ->with(['ratings'])
            ->latest('created_at')
            ->take(3)
            ->get();

        $authorIds = $reviews->pluck('user_id')->filter()->unique();
        $authors = User::whereIn('id', $authorIds)->pluck('name', 'id');

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'joined_year' => optional($user->created_at)->format('Y'),
                'bio' => $user->bio,
                'email_verified_at' => optional($user->email_verified_at)->toIso8601String(),
                'is_verified' => $user->hasVerifiedEmail(),
                'is_legit' => (bool) optional($user->partnerProfile)->is_legit,
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
            ],
            'services' => PartnerServiceResource::collection($services)->resolve(),
            'reviews' => $reviews->map(function ($r) use ($authors) {
                $rating = optional($r->ratings->firstWhere('key', 'rating'))->value
                    ?? optional($r->ratings->firstWhere('key', 'overall'))->value;

                return [
                    'id' => $r->id,
                    'author' => $authors[$r->user_id] ?? null,
                    'rating' => $rating,
                    'review' => $r->review,
                    'created_human' => optional($r->created_at)->diffForHumans(),
                ];
            }),
            'intro' => $user->bio ?? null,
        ];
    }

    private function calcCancelPct(User $user): string
    {
        $total = $user->partnerBillsAsClient()->count();
        if ($total === 0) {
            return '0%';
        }
        $cancel = $user->partnerBillsAsClient()->where('status', 'cancelled')->count();
        return round($cancel * 100 / $total) . '%';
    }
}
