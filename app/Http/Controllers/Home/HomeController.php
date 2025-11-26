<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\AppBannerResource;
use App\Models\Banner;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public const INITIAL_EVENT_CATEGORY_LIMIT = 8;
    public const EVENT_CATEGORY_BATCH_SIZE = 4;
    public const CHILD_CATEGORY_LIMIT = 5;

    private ?int $parentCategoryCount = null;

    /**
     * Show the home page.
     */
    public function index(AppSettings $settings): Response
    {

        $app_partner_banner = Banner::where('type', 'partner')->first()?->getMedia('banners') ?? collect();
        $app_partner_banner_mobile = Banner::where('type', 'mobile_partner')->first()?->getMedia('banners') ?? collect();

        $initialData = $this->fetchEventCategories(self::INITIAL_EVENT_CATEGORY_LIMIT, 0);

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
                'banner_images' => AppBannerResource::collection($app_partner_banner),
                'mobile_banner_images' => AppBannerResource::collection($app_partner_banner_mobile),
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
        $data = $this->fetchEventCategories($limit, $offset);

        return response()->json([
            'eventCategories' => $data['eventCategories'],
            'partnerCategories' => $data['partnerCategories'],
            'hasMore' => ($offset + $limit) < $this->getParentCategoryCount(),
        ]);
    }

    public function loadMoreChildren(Request $request)
    {
        $validated = $request->validate([
            'category_slug' => 'required|string|exists:partner_categories,slug',
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $category = PartnerCategory::whereNull('parent_id')
            ->where('slug', $validated['category_slug'])
            ->firstOrFail();

        $offset = (int) ($validated['offset'] ?? 0);
        $limit = (int) ($validated['limit'] ?? self::CHILD_CATEGORY_LIMIT);

        $childrenQuery = $category->children()->orderBy('order', 'asc');
        $totalChildren = (clone $childrenQuery)->count();

        $children = $childrenQuery
            ->skip($offset)
            ->take($limit)
            ->with('media')
            ->get()
            ->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'min_price' => $child->min_price,
                    'max_price' => $child->max_price,
                    'image' => $this->getImageUrl($child),
                ];
            });

        $hasMore = ($offset + $limit) < $totalChildren;

        return response()->json([
            'children' => $children,
            'hasMore' => $hasMore,
            'total' => $totalChildren,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $term = trim($validated['q']);
        if ($term === '') {
            return response()->json([
                'eventCategories' => [],
                'partnerCategories' => [],
            ]);
        }

        $eventCategories = PartnerCategory::whereNull('parent_id')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhereHas('children', function ($childQuery) use ($term) {
                        $childQuery->where('name', 'like', "%{$term}%")
                            ->orWhere('slug', 'like', "%{$term}%");
                    });
            })
            ->with([
                'media',
                'children' => function ($query) use ($term) {
                    $query->orderBy('order', 'asc')
                        ->limit(self::CHILD_CATEGORY_LIMIT)
                        ->where(function ($child) use ($term) {
                            $child->where('name', 'like', "%{$term}%")
                                ->orWhere('slug', 'like', "%{$term}%");
                        })
                        ->with('media');
                },
            ])
            ->withCount(['children as total_children_count'])
            ->orderBy('order', 'asc')
            ->take(self::INITIAL_EVENT_CATEGORY_LIMIT)
            ->get();

        return response()->json($this->transformEventCategoryResponse($eventCategories));
    }

    public function showCategory(string $categorySlug, AppSettings $settings): Response
    {
        $category = PartnerCategory::whereNull('parent_id')
            ->where('slug', $categorySlug)
            ->with('media')
            ->firstOrFail();

        $children = $category->children()
            ->orderBy('order', 'asc')
            ->with('media')
            ->get()
            ->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'description' => $child->description,
                    'min_price' => $child->min_price,
                    'max_price' => $child->max_price,
                    'image' => $this->getImageUrl($child),
                ];
            });

        return Inertia::render('home/CategoryChildren', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $this->getImageUrl($category),
                'total_children' => $children->count(),
            ],
            'partnerCategories' => $children,
            'settings' => [
                'app_name' => $settings->app_name,
                'hero_title' => $settings->app_partner_title,
            ],
        ]);
    }

    private function fetchEventCategories(int $limit, int $offset): array
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

        return $this->transformEventCategoryResponse($eventCategories);
    }

    private function transformEventCategoryResponse(Collection $eventCategories): array
    {
        $partnerCategories = [];
        foreach ($eventCategories as $category) {
            $partnerCategories[$category->id] = $category->children->map(function ($pc) {
                return [
                    'id' => $pc->id,
                    'name' => $pc->name,
                    'slug' => $pc->slug,
                    'description' => $pc->description,
                    'min_price' => $pc->min_price,
                    'max_price' => $pc->max_price,
                    'image' => $this->getImageUrl($pc),
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

    private function getImageUrl($model)
    {
        if (!method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl('images', 'thumb');
    }
}
