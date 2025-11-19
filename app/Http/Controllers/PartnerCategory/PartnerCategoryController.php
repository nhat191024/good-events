<?php

namespace App\Http\Controllers\PartnerCategory;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use Inertia\Inertia;

class PartnerCategoryController extends Controller
{
    public function show(string $slug)
    {
        $item = PartnerCategory::query()
            ->where('slug', $slug)
            ->firstOrFail();

        // Related: các partner_categories cùng category (trừ chính nó)
        $related = PartnerCategory::query()
            ->where('parent_id', $item->parent_id)
            ->where('id', '!=', $item->id)
            ->latest('updated_at')
            ->take(8)
            ->get(['id', 'name', 'slug', 'min_price', 'max_price']);

        $category = $item->parent;
        $expireAt = now()->addMinutes(3600);

        return Inertia::render('partner-categories/Show', [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'description' => $item->description,
                'updated_human' => $item->updated_at?->diffForHumans(),
                // Ảnh từ media library nếu có
                'image' => $this->getTemporaryImageUrl($item, $expireAt),
            ],
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'related' => $related->map(function ($r) use ($expireAt) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                    'min_price' => $r->min_price,
                    'max_price' => $r->max_price,
                    'image' => $this->getTemporaryImageUrl($r, $expireAt),
                ];
            }),
        ]);
    }

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (! method_exists($model, 'getFirstTemporaryUrl')) {
            return null;
        }

        try {
            return $model->getFirstTemporaryUrl($expireAt, 'images');
        } catch (\Throwable $e) {
            return method_exists($model, 'getFirstMediaUrl')
                ? $model->getFirstMediaUrl('images')
                : null;
        }
    }
}
