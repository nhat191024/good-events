<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\AppBannerResource;
use App\Models\Banner;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use App\Support\SeoPayload;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use RalphJSmit\Laravel\SEO\TagManager;

class HomeController extends Controller
{
    public const int INITIAL_EVENT_CATEGORY_LIMIT = 8;
    public const int EVENT_CATEGORY_BATCH_SIZE = 4;
    public const int CHILD_CATEGORY_LIMIT = 99;

    private ?int $parentCategoryCount = null;

    /**
     * Show the home page.
     */
    public function index(AppSettings $settings, TagManager $tagManager): Response
    {
        $app_partner_banner = Banner::where('type', 'partner')->first()?->getMedia('banners') ?? collect();
        $app_partner_banner_mobile = Banner::where('type', 'mobile_partner')->first()?->getMedia('banners') ?? collect();

        $initialData = $this->fetchEventCategories(self::INITIAL_EVENT_CATEGORY_LIMIT, 0);

        $keywords = collect($initialData['eventCategories'])
            ->pluck('name')
            ->merge(collect($initialData['partnerCategories'])->flatten()->pluck('name'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $seoData = new SEOData(
            title: $settings->app_title,
            description: $settings->app_description,
            url: url('/'),
            canonical_url: url('/'),
            image: $app_partner_banner->first()?->getUrl() ?? null,
            tags: $keywords,
            site_name: $settings->app_name,
        );

        $seo = SeoPayload::toArray($seoData, $tagManager);

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
            'seo' => $seo,
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

        $categories = PartnerCategory::getTree();
        $category = $categories->where('slug', $validated['category_slug'])->firstOrFail();

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

        $allCategories = PartnerCategory::getTree();

        $eventCategories = $allCategories->filter(function ($category) use ($term) {
            if (stripos($category->name, $term) !== false || stripos($category->slug, $term) !== false) {
                return true;
            }

            return $category->children->contains(function ($child) use ($term) {
                return stripos($child->name, $term) !== false || stripos($child->slug, $term) !== false;
            });
        })->take(self::INITIAL_EVENT_CATEGORY_LIMIT)->values();

        $categoriesToLoad = new EloquentCollection();

        $eventCategories->each(function ($category) use ($term, $categoriesToLoad) {
            $categoriesToLoad->push($category);

            $children = $category->children
                ->filter(function ($child) use ($term) {
                    return stripos($child->name, $term) !== false || stripos($child->slug, $term) !== false;
                })
                ->take(self::CHILD_CATEGORY_LIMIT)
                ->values();

            $category->setRelation('children', $children);

            foreach ($children as $child) {
                $categoriesToLoad->push($child);
            }

            $category->setAttribute('total_children_count', $children->count());
        });

        if ($categoriesToLoad->isNotEmpty()) {
            $categoriesToLoad->load('media');
        }

        return response()->json($this->transformEventCategoryResponse($eventCategories));
    }

    public function showCategory(string $categorySlug, AppSettings $settings, TagManager $tagManager): Response
    {
        $categories = PartnerCategory::getTree();
        $category = $categories->where('slug', $categorySlug)->first();
        if (!$category) {
            abort(404);
        }
        $category->load('media');

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

        $seoData = new SEOData(
            title: 'Danh mục ' . $category->name . ' | ' . $settings->app_title,
            description: 'Tìm thấy ' . $children->count() . ' hạng mục liên quan - ' . $settings->app_description,
            url: route('home.category', ['category_slug' => $category->slug]),
            canonical_url: route('home.category', ['category_slug' => $category->slug]),
            image: $this->getImageUrl($category) ?? null,
            tags: $children->pluck('name')->filter()->unique()->values()->all(),
            site_name: $settings->app_name,
        );

        $seo = SeoPayload::toArray($seoData, $tagManager);

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
            'seo' => $seo,
        ]);
    }

    private function fetchEventCategories(int $limit, int $offset): array
    {
        $allCategories = PartnerCategory::getTree();
        $eventCategories = $allCategories->slice($offset, $limit)->values();

        $categoriesToLoad = new EloquentCollection();

        $eventCategories->each(function ($category) use ($categoriesToLoad) {
            $categoriesToLoad->push($category);

            $children = $category->children->take(self::CHILD_CATEGORY_LIMIT)->values();

            $category->setRelation('children', $children);

            foreach ($children as $child) {
                $categoriesToLoad->push($child);
            }

            $category->setAttribute('total_children_count', $children->count());
        });

        if ($categoriesToLoad->isNotEmpty()) {
            $categoriesToLoad->load('media');
        }

        return $this->transformEventCategoryResponse($eventCategories);
    }

    private function transformEventCategoryResponse(Collection $eventCategories): array
    {
        $partnerCategories = [];
        foreach ($eventCategories as $category) {
            $partnerCategories[$category->id] = $category->children
                ->values()
                ->map(function ($pc) {
                    return [
                        'id' => $pc->id,
                        'name' => $pc->name,
                        'slug' => $pc->slug,
                        'description' => $pc->description,
                        'min_price' => $pc->min_price,
                        'max_price' => $pc->max_price,
                        'image' => $this->getImageUrl($pc),
                    ];
                })
                ->values();
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

        $this->parentCategoryCount = PartnerCategory::getTree()->count();

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
