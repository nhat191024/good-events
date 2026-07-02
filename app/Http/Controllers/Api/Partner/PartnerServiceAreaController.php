<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\CacheKey;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\UpsertPartnerServiceAreasRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PartnerServiceAreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user->loadMissing('partnerServiceAreas.location.province');

        return response()->json($this->serviceAreaPayload($user));
    }

    public function store(UpsertPartnerServiceAreasRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        foreach ($this->validWardLocationIds($request->input('location_ids', [])) as $locationId) {
            $user->partnerServiceAreas()->firstOrCreate([
                'location_id' => $locationId,
            ]);
        }

        $this->flushServiceAreaCache();
        $user->load('partnerServiceAreas.location.province');

        return response()->json([
            'success' => true,
            ...$this->serviceAreaPayload($user),
        ]);
    }

    public function update(UpsertPartnerServiceAreasRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $locationIds = $this->validWardLocationIds($request->input('location_ids', []));

        if (empty($locationIds)) {
            $user->partnerServiceAreas()->delete();
        } else {
            $user->partnerServiceAreas()
                ->whereNotIn('location_id', $locationIds)
                ->delete();

            foreach ($locationIds as $locationId) {
                $user->partnerServiceAreas()->firstOrCreate([
                    'location_id' => $locationId,
                ]);
            }
        }

        $this->flushServiceAreaCache();
        $user->load('partnerServiceAreas.location.province');

        return response()->json([
            'success' => true,
            ...$this->serviceAreaPayload($user),
        ]);
    }

    /**
     * @param array<int, int|string> $locationIds
     * @return list<int>
     */
    private function validWardLocationIds(array $locationIds): array
    {
        return Location::query()
            ->whereIn('id', collect($locationIds)->map(fn ($id): int => (int) $id)->unique()->values())
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function flushServiceAreaCache(): void
    {
        Cache::tags([CacheKey::PARTNER_SERVICE_AREAS->value])->flush();
    }

    /**
     * @return array{service_area_location_ids: array<int, int>, service_areas: array<int, array{id: int, name: string|null, province_id: int|null, province_name: string|null}>}
     */
    private function serviceAreaPayload(User $user): array
    {
        return [
            'service_area_location_ids' => $user->partnerServiceAreas
                ->pluck('location_id')
                ->values()
                ->all(),
            'service_areas' => $user->partnerServiceAreas
                ->map(fn ($serviceArea): array => [
                    'id' => $serviceArea->location_id,
                    'name' => $serviceArea->location?->name,
                    'province_id' => $serviceArea->location?->parent_id,
                    'province_name' => $serviceArea->location?->province?->name,
                ])
                ->values()
                ->all(),
        ];
    }
}
