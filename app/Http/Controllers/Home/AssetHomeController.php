<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\AppBannerResource;
use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\FileProductResource;
use App\Http\Resources\Home\TagResource;
use App\Models\Banner;
use App\Models\DesignCategory;
use App\Models\FileProduct;
use App\Models\Tag;
use App\Models\Taggable;
use App\Settings\AppSettings;
use App\Support\SeoPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use RalphJSmit\Laravel\SEO\TagManager;

class AssetHomeController extends Controller
{
    public const RECORD_PER_PAGE = 20;

    /**
     * Show the home page.
     */
    public function index(Request $request, AppSettings $settings, TagManager $tagManager): Response
    {
        $page = max(1, (int) $request->query('page', 1));

        $fileProducts = FileProduct::with('category.parent', 'category', 'tags', 'media')
            ->orderBy('created_at', 'desc')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        $appDesignBanner = optional(Banner::whereType('design')->first())
                ?->getMedia('banners') ?? collect();

        $tags = Taggable::getModelTags('FileProduct');
        $categories = DesignCategory::limit(15)
            ->where('type', 'design')
            ->orderBy('order', 'asc')
            ->whereParentId(null)
            ->whereIsShow(1)
            ->with('media')
            ->get();

        $resourcedCategories = CategoryResource::collection($categories);

        $seoData = new SEOData(
            title: 'Kho file thiết kế sự kiện tốt' . ' | ' . $settings->app_title,
            description: 'Thiết kế sự kiện chuyên nghiệp - Photoshop, backdrop, banner, ấn phẩm' . ' - ' . $settings->app_description,
            url: route('asset.home'),
            canonical_url: route('asset.home'),
            image: $appDesignBanner->first()?->getUrl() ?? null,
            tags: $resourcedCategories->pluck('name')->toArray(),
            site_name: $settings->app_name,
        );

        $seo = SeoPayload::toArray($seoData, $tagManager);

        return Inertia::render('home/AssetHome', [
            'fileProducts' => FileProductResource::collection($fileProducts),
            'tags' => TagResource::collection($tags),
            'categories' => $resourcedCategories,
            'settings' => [
                'app_name' => $settings->app_name,
                'banner_images' => AppBannerResource::collection($appDesignBanner),
                'hero_title' => $settings->app_design_title,
            ],
            'seo' => $seo,
        ]);
    }
}
