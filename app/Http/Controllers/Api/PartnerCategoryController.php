<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;

class PartnerCategoryController extends Controller
{
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

        $related = PartnerCategory::query()
            ->where('parent_id', $item->parent_id)
            ->where('id', '!=', $item->id)
            ->latest('updated_at')
            ->take(8)
            ->get(['id', 'name', 'slug', 'min_price', 'max_price']);

        $category = $item->parent;

        return response()->json([
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'description' => $item->description,
                'updated_human' => $item->updated_at?->diffForHumans(),
                'image' => $this->getImageUrl($item),
            ],
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'related' => $related->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'min_price' => $r->min_price,
                'max_price' => $r->max_price,
                'image' => $this->getImageUrl($r),
            ])->values(),
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
