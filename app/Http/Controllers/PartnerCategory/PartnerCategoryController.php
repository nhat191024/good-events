<?php

namespace App\Http\Controllers\PartnerCategory;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use App\Support\SeoPayload;
use Illuminate\Support\Str;
use Inertia\Inertia;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use RalphJSmit\Laravel\SEO\TagManager;

class PartnerCategoryController extends Controller
{
    public function show(string $slug, AppSettings $settings, TagManager $tagManager)
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

        $descText = Str::words(strip_tags($item->description), 20, '…');

        $seoData = new SEOData(
            title: 'Thuê nhân sự tổ chức sự kiện ' . $item->name . ' | ' . $settings->app_title,
            description: 'Đối tác uy tín đã được xác minh. Phục vụ chuyên nghiệp tận tâm. Giá cả cạnh tranh minh bạch. - ' . $descText . ' - ' . $settings->app_description,
            url: route('partner-categories.show', ['slug' => $item->slug]),
            canonical_url: route('partner-categories.show', ['slug' => $item->slug]),
            image: $this->getImageUrl($item) ?? null,
            tags: array_merge($related->pluck('name')->toArray(), [$item->name, $category?->name ?? '']),
            site_name: $settings->app_name,
        );

        $seo = SeoPayload::toArray($seoData, $tagManager);

        return Inertia::render('partner-categories/Show', [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'min_price' => $item->min_price,
                'max_price' => $item->max_price,
                'description' => $item->description,
                'video_url' => $item->video_url,
                'updated_human' => $item->updated_at?->diffForHumans(),
                // Ảnh từ media library nếu có
                'image' => $this->getImageUrl($item),
            ],
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ] : null,
            'related' => $related->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'min_price' => $r->min_price,
                'max_price' => $r->max_price,
                'image' => $this->getImageUrl($r),
            ]),
            'seo' => $seo,
        ]);
    }

    private function getImageUrl($model)
    {
        if (!method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl('images', 'thumb');
    }
}
