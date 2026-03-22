<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\CacheKey;
use App\Models\PartnerCategory;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;

class PartnerCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = Cache::remember(CacheKey::PARTNER_CATEGORY_WITHOUT_PARENT->value, 600, function () {
            return PartnerCategory::query()
                ->whereNull('parent_id')
                ->get();
        });

        return response()->json($categories->map(function ($category) {
            return [
                'id' => (string) $category->id,
                'name' => $category->name,
            ];
        }));
    }
}
