<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\PartnerCategory;
use App\Http\Controllers\Controller;

class PartnerCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = PartnerCategory::query()
            ->whereNotNull('parent_id')
            ->get();

        return response()->json($categories->map(function ($category) {
            return [
                'id' => (string) $category->id,
                'name' => $category->name,
            ];
        }));
    }
}
