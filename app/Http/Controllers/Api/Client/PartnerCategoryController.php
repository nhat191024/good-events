<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;

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
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return response()->json($categories->map(function ($category) {
            return [
                'id' => (string) $category->id,
                'name' => $category->name,
                'image' => $this->getImageUrl($category),
                'partnerList' => $category->children->map(function ($child) {
                    return [
                        'id' => (string) $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'image' => $this->getImageUrl($child),
                    ];
                }),
            ];
        }));
    }

    /**
     * GET /api/partner-categories/{slug}
     *
     * Response: { item, category, related }
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $slug)
    {
        $item = PartnerCategory::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $category = $item->parent;

        return response()->json([
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'updated_human' => $item->updated_at?->diffForHumans(),
                'image' => $this->getImageUrl($item),
                'video_url' => $item->video_url,
                'description' => $item->description,
            ],
        ]);
    }

    private function getImageUrl($model): ?string
    {
        if (!method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl('images', 'thumb');
    }
}
