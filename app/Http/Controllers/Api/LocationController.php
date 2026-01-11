<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    // return wards for a given parent location
    // so we dont have to send over 3k records to the client each time
    /**
     * GET /api/locations/{location}/wards
     *
     * Response: list of ward locations
     *
     * @param Location $location
     * @return JsonResponse
     */
    public function wards(Location $location): JsonResponse
    {
        $cacheKey = "location:{$location->id}:wards";

        $wards = Cache::remember($cacheKey, 60 * 60, function () use ($location) {
            return $location->wards()
                ->select(['id', 'name', 'code', 'codename', 'short_codename', 'parent_id'])
                ->orderBy('name')
                ->get();
        });

        return response()->json($wards);
    }
}
