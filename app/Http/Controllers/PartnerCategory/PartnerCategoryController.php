<?php

namespace App\Http\Controllers\PartnerCategory;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartnerCategoryController extends Controller
{
    public function show(string $slug)
    {
        $item = PartnerCategory::query()
            ->with(['category.parent'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Related: các partner_categories cùng category (trừ chính nó)
        $related = PartnerCategory::query()
            ->where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->latest('updated_at')
            ->take(8)
            ->get(['id','name','slug','min_price','max_price']);

        $category = $item->category;               // danh mục con
        $parent   = $category?->parent;            // danh mục cha (top-level)

        return Inertia::render('partner-categories/Show', [
            'item' => [
                'id'          => $item->id,
                'name'        => $item->name,
                'slug'        => $item->slug,
                'min_price'   => $item->min_price,
                'max_price'   => $item->max_price,
                'description' => $item->description,
                'updated_human' => $item->updated_at?->diffForHumans(),
                // ảnh dùng placeholder theo yêu cầu
                'image'       => null,
            ],
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'parent' => $parent ? [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
            ] : null,
            'related' => $related->map(function ($r) {
                return [
                    'id'        => $r->id,
                    'name'      => $r->name,
                    'slug'      => $r->slug,
                    'min_price' => $r->min_price,
                    'max_price' => $r->max_price,
                    'image'     => null,
                ];
            }),
        ]);
    }
}
