<?php

namespace App\Http\Controllers\Api;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerProfile;
use App\Services\EmailVerificationMailService;
use App\Settings\PartnerSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(Role::CLIENT);

        app(EmailVerificationMailService::class)->sendVerificationLink($user);

        $user->loadMissing('partnerProfile');
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => Role::CLIENT->value,
            'user' => new UserResource($user),
        ]);
    }

    public function registerPartner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'identity_card_number' => 'required|string|max:50|unique:partner_profiles,identity_card_number',
            'ward_id' => 'required|integer|exists:locations,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'ward_id.required' => 'Please select a ward.',
            'ward_id.exists' => 'Invalid ward.',
        ]);

        $ward = Location::findOrFail($validated['ward_id']);

        $user = Partner::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(Role::PARTNER);

        $defaultBalance = app(PartnerSettings::class)->default_balance;
        if ($defaultBalance > 0) {
            $meta = [
                'reason' => __('admin/partner.messages.app_deposit'),
                'old_balance' => $user->balanceInt,
                'new_balance' => $user->balanceInt + $defaultBalance,
            ];
            $user->deposit($defaultBalance, $meta)->save();
        }

        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_name' => $validated['name'],
            'identity_card_number' => $validated['identity_card_number'],
            'location_id' => $ward->id,
        ]);

        app(EmailVerificationMailService::class)->sendVerificationLink($user);

        $user->loadMissing('partnerProfile');
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => Role::PARTNER->value,
            'user' => new UserResource($user),
        ]);
    }
}
