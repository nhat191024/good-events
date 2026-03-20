<?php

namespace App\Http\Controllers\Api\Common;

use App\Enum\CacheKey;
use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    /**
     * GET /api/locations/{location}/wards
     *
     * Response: list of ward locations
     */
    public function wards(Location $location): JsonResponse
    {
        $cacheKey = CacheKey::LOCATION_WARDS->value . $location->id;

        $wards = Cache::rememberForever($cacheKey, function () use ($location) {
            return $location->wards()
                ->select(['id', 'name', 'code', 'codename', 'short_codename', 'parent_id'])
                ->orderBy('name')
                ->get();
        });

        return response()->json($wards);
    }

    public function provinces(): JsonResponse
    {
        $cacheKey = CacheKey::LOCATION_PROVINCES->value;

        $provinces = Cache::rememberForever($cacheKey, function () {
            return Location::query()
                ->whereNull('parent_id')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });

        return response()->json($provinces);
    }
}
