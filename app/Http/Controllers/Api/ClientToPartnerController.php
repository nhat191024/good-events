<?php

namespace App\Http\Controllers\Api;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class ClientToPartnerController extends Controller
{
    public function form(Request $request): JsonResponse
    {
        $user = $request->user();
        $isPartner = $user?->hasRole(Role::PARTNER->value) ?? false;

        $user?->loadMissing('partnerProfile');

        $provinces = Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name', 'code', 'codename', 'short_codename', 'type', 'phone_code', 'parent_id'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'user' => $user ? new UserResource($user) : null,
            'is_partner' => $isPartner,
            'provinces' => LocationResource::collection($provinces),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'identity_card_number' => 'required|string|min:12|max:50|unique:partner_profiles,identity_card_number',
            'ward_id' => 'required|integer|exists:locations,id',
        ], [
            'identity_card_number.required' => 'Please provide your identity card number.',
            'ward_id.required' => 'Please select a ward.',
            'ward_id.integer' => 'Invalid ward.',
            'ward_id.exists' => 'Invalid ward.',
        ]);

        $ward = Location::findOrFail($validated['ward_id']);

        try {
            DB::beginTransaction();

            PartnerProfile::create([
                'user_id' => $authUser->id,
                'partner_name' => $authUser->name,
                'identity_card_number' => $validated['identity_card_number'],
                'location_id' => $ward->id,
            ]);

            $asCustomer = Customer::find($authUser->id);

            $authUser->syncRoles([]);
            if ($asCustomer) {
                $asCustomer->syncRoles([]);
            }

            $asPartner = Partner::findOrFail($authUser->id);
            $asPartner->assignRole(Role::PARTNER->value);

            DB::commit();

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return response()->json([
                'success' => true,
                'message' => 'Partner registration completed.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error upgrading user to partner: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade to partner. Please try again.',
            ], 500);
        }
    }
}
