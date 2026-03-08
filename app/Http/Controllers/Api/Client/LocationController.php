<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    // return wards for a given parent location
    // so we dont have to send over 3k records to the client each time
    /**
     * GET /api/locations/{location}/wards
     *
     * Response: list of ward locations
     */
    public function wards(Location $location): JsonResponse
    {
        $cacheKey = "location:{$location->id}:wards";

        $wards = Cache::remember($cacheKey, 60 * 9999, function () use ($location) {
            return $location->wards()
                ->select(['id', 'name', 'code', 'codename', 'short_codename', 'parent_id'])
                ->orderBy('name')
                ->get();
        });

        return response()->json($wards);
    }

    public function provinces(): JsonResponse
    {
        $cacheKey = 'locations:provinces';

        $provinces = Cache::remember($cacheKey, 60 * 9999, function () {
            return Location::query()
                ->whereNull('parent_id')
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });

        return response()->json($provinces);
    }
}
