<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\AppBannerResource;
use App\Models\Banner;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public const INITIAL_EVENT_CATEGORY_LIMIT = 8;
    public const EVENT_CATEGORY_BATCH_SIZE = 4;
    public const CHILD_CATEGORY_LIMIT = 4;

    private ?int $parentCategoryCount = null;

    /**
     * Show the home page.
     */
    public function index(AppSettings $settings): Response
    {

        $expireAt = now()->addMinutes(3600);

        $app_partner_banner = Banner::where('type', 'partner')->first()->getMedia('banners');

        $initialData = $this->fetchEventCategories(self::INITIAL_EVENT_CATEGORY_LIMIT, 0, $expireAt);

        $bannerImages = AppBannerResource::collection($app_partner_banner);

        return Inertia::render('home/Home', [
            'eventCategories' => $initialData['eventCategories'],
            'partnerCategories' => $initialData['partnerCategories'],
            'pagination' => [
                'total' => $this->getParentCategoryCount(),
                'initialLimit' => self::INITIAL_EVENT_CATEGORY_LIMIT,
                'batchSize' => self::EVENT_CATEGORY_BATCH_SIZE,
                'childLimit' => self::CHILD_CATEGORY_LIMIT,
            ],
            'settings' => [
                'app_name' => $settings->app_name,
                'banner_images' => $bannerImages,
                'hero_title' => $settings->app_partner_title,
            ],
        ]);
    }

    public function loadMoreCategories(Request $request)
    {
        $validated = $request->validate([
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? self::EVENT_CATEGORY_BATCH_SIZE);
        $expireAt = now()->addMinutes(3600);

        $data = $this->fetchEventCategories($limit, $offset, $expireAt);

        return response()->json([
            'eventCategories' => $data['eventCategories'],
            'partnerCategories' => $data['partnerCategories'],
            'hasMore' => ($offset + $limit) < $this->getParentCategoryCount(),
        ]);
    }

    public function showCategory(string $categorySlug, AppSettings $settings): Response
    {
        $expireAt = now()->addMinutes(3600);

        $category = PartnerCategory::whereNull('parent_id')
            ->where('slug', $categorySlug)
            ->with('media')
            ->firstOrFail();

        $children = $category->children()
            ->orderBy('order', 'asc')
            ->with('media')
            ->get()
            ->map(function ($child) use ($expireAt) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'min_price' => $child->min_price,
                    'max_price' => $child->max_price,
                    'image' => $this->getTemporaryImageUrl($child, $expireAt),
                ];
            });

        return Inertia::render('home/CategoryChildren', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $this->getTemporaryImageUrl($category, $expireAt),
                'total_children' => $children->count(),
            ],
            'partnerCategories' => $children,
            'settings' => [
                'app_name' => $settings->app_name,
                'hero_title' => $settings->app_partner_title,
            ],
        ]);
    }

    private function fetchEventCategories(int $limit, int $offset, $expireAt): array
    {
        $eventCategories = PartnerCategory::whereNull('parent_id')
            ->with([
                'media',
                'children' => function ($query) {
                    $query->orderBy('order', 'asc')
                        ->limit(self::CHILD_CATEGORY_LIMIT)
                        ->with('media');
                },
            ])
            ->withCount(['children as total_children_count'])
            ->orderBy('order', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $partnerCategories = [];
        foreach ($eventCategories as $category) {
            $partnerCategories[$category->id] = $category->children->map(function ($pc) use ($expireAt) {
                return [
                    'id' => $pc->id,
                    'name' => $pc->name,
                    'slug' => $pc->slug,
                    'description' => $pc->description,
                    'min_price' => $pc->min_price,
                    'max_price' => $pc->max_price,
                    'image' => $this->getTemporaryImageUrl($pc, $expireAt),
                ];
            });
        }

        return [
            'eventCategories' => $eventCategories->values(),
            'partnerCategories' => $partnerCategories,
        ];
    }

    private function getParentCategoryCount(): int
    {
        if ($this->parentCategoryCount !== null) {
            return $this->parentCategoryCount;
        }

        $this->parentCategoryCount = PartnerCategory::whereNull('parent_id')->count();

        return $this->parentCategoryCount;
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
