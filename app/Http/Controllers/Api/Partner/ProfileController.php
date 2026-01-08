<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\PartnerProfileResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Location;
use App\Models\PartnerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user->loadMissing('partnerProfile');
        $partnerProfile = $user->partnerProfile;
        $cityName = null;
        $provinceName = null;
        $wards = collect();

        if ($partnerProfile?->location_id) {
            $location = Location::find($partnerProfile->location_id);
            $city = null;

            if ($location) {
                $city = $location->parent_id
                    ? Location::find($location->parent_id)
                    : $location;
            }

            if ($city) {
                $province = $city->parent_id
                    ? Location::find($city->parent_id)
                    : $city;

                $cityName = $city->name;
                $provinceName = $province?->name;

                $wards = Location::query()
                    ->where('parent_id', $city->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return response()->json([
            'user' => new UserResource($user),
            // 'partner_profile' => $partnerProfile ? new PartnerProfileResource($partnerProfile) : null,
            'city_name' => $cityName,
            'province_name' => $provinceName,
            // 'wards' => LocationResource::collection($wards),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'country_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:25'],
            'bio' => ['nullable', 'string', 'max:255'],
            'partner_name' => ['required', 'string', 'max:255'],
            'identity_card_number' => ['required', 'string', 'max:50'],
            'city_id' => ['nullable', 'integer', 'exists:locations,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'avatar' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
            'selfie_image' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
            'front_identity_card_image' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
            'back_identity_card_image' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'country_code' => $validated['country_code'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

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

        $partnerProfile = $user->partnerProfile;
        $partnerData = [
            'partner_name' => $validated['partner_name'],
            'location_id' => $validated['location_id'],
            'identity_card_number' => $validated['identity_card_number'],
        ];

        if ($request->hasFile('selfie_image')) {
            $partnerData['selfie_image'] = $this->storePartnerImage($request->file('selfie_image'), $user->id, 'selfies');
        }
        if ($request->hasFile('front_identity_card_image')) {
            $partnerData['front_identity_card_image'] = $this->storePartnerImage($request->file('front_identity_card_image'), $user->id, 'id_cards');
        }
        if ($request->hasFile('back_identity_card_image')) {
            $partnerData['back_identity_card_image'] = $this->storePartnerImage($request->file('back_identity_card_image'), $user->id, 'id_cards');
        }

        if ($partnerProfile) {
            $partnerProfile->update($partnerData);
        } else {
            PartnerProfile::create([
                'user_id' => $user->id,
                ...$partnerData,
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->refresh()->loadMissing('partnerProfile')),
            'partner_profile' => $user->partnerProfile ? new PartnerProfileResource($user->partnerProfile) : null,
        ]);
    }

    private function storePartnerImage($file, int $userId, string $subDir): string
    {
        $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();
        $directory = 'uploads/partner_profiles/' . $userId . '/' . $subDir;

        return $file->storeAs($directory, $filename, 'public');
    }
}
