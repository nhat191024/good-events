<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\Role;
use App\Enum\PartnerBillStatus;
use App\Enum\StatisticType;

use App\Models\User;

use App\Http\Controllers\Controller;

use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\Api\PartnerServiceResource;
use App\Http\Resources\Api\ProfileResource;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

use Cohensive\OEmbed\Facades\OEmbed;

class ProfileController extends Controller
{
    /**
     * GET /api/profile/me
     *
     * Response: { profile: ProfileResource|null }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->load(['partnerProfile.location.province']);

        $stats = $user->statistics()->get()->keyBy('metrics_name');

        $completedOrdersStat = optional($stats->get(StatisticType::COMPLETED_ORDERS->value))->metrics_value;
        $cancelledOrdersPctStat = optional($stats->get(StatisticType::CANCELLED_ORDERS_PERCENTAGE->value))->metrics_value;

        $profile = new ProfileResource($user);
        $profileArray = $profile->toArray($request);
        $profileArray['stats'] = [
            'completed_orders' => $completedOrdersStat !== null ? (int) $completedOrdersStat : null,
            'cancelled_orders_pct' => $cancelledOrdersPctStat !== null ? (string) $cancelledOrdersPctStat : null,
        ];

        return response()->json([
            'profile' => $profileArray,
        ]);
    }

    /**
     * POST /api/profile/update (multipart/form-data)
     *
     * Body: name, email, country_code, phone, bio, avatar (file)
     * Response: { success: true, user }
     *
     * @param ProfileUpdateRequest $request
     * @return JsonResponse
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $partnerImageFields = ['selfie_image', 'front_identity_card_image', 'back_identity_card_image'];
        $excludedFromFill = array_merge(['avatar'], $partnerImageFields);
        $validated = $request->safe()->except($excludedFromFill);

        $user->fill(array_intersect_key($validated, array_flip(['name', 'email', 'country_code', 'phone', 'bio'])));

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

        if ($user->hasRole(Role::PARTNER)) {
            $partnerProfile = $user->partnerProfile ?? $user->partnerProfile()->create([
                'partner_name' => $user->name,
                'user_id' => $user->id,
            ]);

            $videoUrl = $validated['video_url'] ?? null;

            if ($videoUrl) {
                try {
                    if (str_contains($videoUrl, '/shorts/')) {
                        $videoUrl = strtok($videoUrl, '?');
                        $videoUrl = str_replace('/shorts/', '/watch?v=', $videoUrl);
                    }

                    if (!str_contains($videoUrl, 'www.') && str_contains($videoUrl, 'youtube.com')) {
                        $videoUrl = str_replace('youtube.com', 'www.youtube.com', $videoUrl);
                    }

                    $embed = OEmbed::get($videoUrl);
                    if ($embed) {
                        $videoUrl = $embed->html([
                            'width' => 640,
                            'height' => 360,
                        ]);
                    } else {
                        $videoUrl = null;
                    }
                } catch (\Exception $e) {
                    $videoUrl = null;
                }
            }

            $partnerFillable = array_filter(
                array_intersect_key($validated, array_flip(['partner_name', 'identity_card_number', 'location_id'])),
                fn($value) => $value !== null
            );

            if ($videoUrl !== null || array_key_exists('video_url', $validated)) {
                $partnerFillable['video_url'] = $videoUrl;
            }

            $partnerProfile->fill($partnerFillable);

            foreach ($partnerImageFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('uploads/partner', $filename, 'public');

                    $oldPath = $partnerProfile->getOriginal($field);
                    if ($oldPath && !Str::startsWith($oldPath, ['http://', 'https://'])) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $partnerProfile->{$field} = $path;
                }
            }

            $partnerProfile->save();
        }

        $user->loadMissing('partnerProfile');

        return response()->json([
            'success' => true,
            'user' => new ProfileResource($user),
        ]);
    }

    /**
     * GET /api/profile/{user}
     *
     * Path: user (id)
     * Response: { profile_type: "client"|"partner", payload }
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $user->loadMissing('partnerProfile');

        $isPartner = $user->partnerProfile;

        $payload = $isPartner
            ? $this->partnerPayload($user)
            : $this->clientPayload($user);

        return response()->json([
            'profile_type' => $isPartner ? 'partner' : 'client',
            'payload' => $payload,
        ]);
    }

    /**
     * POST /api/profile/password
     *
     * Body: current_password, password, password_confirmation
     * Response: { success: true }
     *
     * @param Request $request
     * @return JsonResponse
     */
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
