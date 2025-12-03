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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssetHomeController extends Controller
{
    public const RECORD_PER_PAGE = 20;

    /**
     * Show the home page.
     */
    public function index(Request $request, AppSettings $settings): Response
    {
        $page = max(1, (int) $request->query('page', 1));

        $fileProducts = FileProduct::with('category.parent', 'category', 'tags', 'media')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        $appDesignBanner = optional(Banner::where('type', 'design')->first())
            ?->getMedia('banners') ?? collect();

        $tags = Taggable::getModelTags('FileProduct');
        $categories = DesignCategory::limit(15)
            ->where('type', 'design')
            ->orderBy('order', 'asc')
            ->whereParentId(null)
            ->whereIsShow(1)
            ->get();

        return Inertia::render('home/AssetHome', [
            'fileProducts' => FileProductResource::collection($fileProducts),
            'tags' => TagResource::collection($tags),
            'categories' => CategoryResource::collection($categories),
            'settings' => [
                'app_name' => $settings->app_name,
                'banner_images' => AppBannerResource::collection($appDesignBanner),
                'hero_title' => $settings->app_design_title,
            ],
        ]);
    }
}
