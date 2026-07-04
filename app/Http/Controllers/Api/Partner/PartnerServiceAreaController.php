<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\CacheKey;
use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\UpsertPartnerServiceAreasRequest;
use App\Models\Location;
use App\Models\PartnerServiceArea;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class PartnerServiceAreaController extends Controller
{
    private const int DEFAULT_PER_PAGE = 50;
    private const int MAX_PER_PAGE = 100;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole(Role::PARTNER)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($this->paginatedServiceAreaPayload($user, $request));
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

        return response()->json([
            'success' => true,
            ...$this->paginatedServiceAreaPayload($user, $request),
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

        return response()->json([
            'success' => true,
            ...$this->paginatedServiceAreaPayload($user, $request),
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
     * @return array{service_area_location_ids: list<int>, service_areas: list<array{id: int, name: string|null, province_id: int|null, province_name: string|null}>, meta: array{current_page: int, per_page: int, has_more_pages: bool}}
     */
    private function paginatedServiceAreaPayload(User $user, Request $request): array
    {
        $paginator = $user->partnerServiceAreas()
            ->select(['id', 'user_id', 'location_id'])
            ->with([
                'location:id,parent_id,name',
                'location.province:id,name',
            ])
            ->orderBy('location_id')
            ->simplePaginate(
                $this->resolvePerPage($request),
                ['*'],
                'page',
                max(1, (int) $request->query('page', 1)),
            );

        /** @var \Illuminate\Support\Collection<int, PartnerServiceArea> $serviceAreas */
        $serviceAreas = $paginator->getCollection();

        return [
            'service_area_location_ids' => $serviceAreas
                ->pluck('location_id')
                ->map(fn ($locationId): int => (int) $locationId)
                ->values()
                ->all(),
            'service_areas' => $serviceAreas
                ->map(fn (PartnerServiceArea $serviceArea): array => [
                    'id' => (int) $serviceArea->location_id,
                    'name' => $serviceArea->location?->name,
                    'province_id' => $serviceArea->location?->parent_id,
                    'province_name' => $serviceArea->location?->province?->name,
                ])
                ->values()
                ->all(),
            'meta' => $this->paginationMeta($paginator),
        ];
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = max(1, (int) $request->query('per_page', self::DEFAULT_PER_PAGE));

        return min(self::MAX_PER_PAGE, $perPage);
    }

    /**
     * @return array{current_page: int, per_page: int, has_more_pages: bool}
     */
    private function paginationMeta(Paginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
