<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

use App\Enum\CacheKey;

use App\Models\Category;
use App\Models\RentProduct;
use App\Models\Taggable;

use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\RentProductResource;
use App\Http\Resources\Home\TagResource;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RentController extends Controller
{
    private const DISCOVER_PER_PAGE = 12;

    public function rentDiscover(Request $request): Response|RedirectResponse
    {
        return $this->renderDiscoverPage($request, null);
    }

    public function rentCategory(Request $request, string $categorySlug): Response|RedirectResponse
    {
        $category = Category::with('parent')
            ->where('slug', $categorySlug)
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function rentDetail(Request $request, string $categorySlug, string $rentProductSlug): Response
    {
        $rentProduct = RentProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->whereHas('category', fn($builder) => $builder->where('slug', $categorySlug))
            ->where('slug', $rentProductSlug)
            ->firstOrFail();

        $previewImages = $this->buildPreviewImages($rentProduct);

        $related = RentProduct::query()
            ->with(['tags', 'media'])
            ->where('category_id', $rentProduct->category_id)
            ->whereKeyNot($rentProduct->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        // Reuse the already-loaded category to avoid duplicate queries
        $related->each(fn($product) => $product->setRelation('category', $rentProduct->category));

        $rentProductPayload = array_merge(
            RentProductResource::make($rentProduct)->resolve($request),
            [
                'long_description' => $rentProduct->description,
                'highlights' => [],
                'preview_images' => $previewImages,
                'tags' => TagResource::collection($rentProduct->tags)->resolve($request),
                'updated_human' => optional($rentProduct->updated_at)->diffForHumans(),
            ]
        );

        return Inertia::render('rent/Detail', [
            'rentProduct' => $rentProductPayload,
            'related' => RentProductResource::collection($related)->resolve($request),
            'contactHotline' => config('services.rent.hotline', '0901 234 567'),
        ]);
    }

    private function renderDiscoverPage(Request $request, ?Category $category = null): Response|RedirectResponse
    {
        [$search, $tagSlugs, $page] = $this->getRequestParams($request);

        if ($redirect = $this->getRedirectResponse($request, $category, $search, $tagSlugs, $page)) {
            return $redirect;
        }

        $childCategories = $category ? $this->fetchChildCategories($category) : new Collection();

        $rentProducts = $this->fetchRentProducts($category, $childCategories, $search, $tagSlugs, $page);

        $categories = $this->fetchSidebarCategories($category);
        $tags = $this->fetchSidebarTags();

        return Inertia::render('rent/Discover', [
            'rentProducts' => RentProductResource::collection($rentProducts),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'childCategories' => CategoryResource::collection($childCategories),
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'tags' => $tagSlugs->values()->all(),
            ],
        ]);
    }

    /**
     * @return array{0: string, 1: Collection, 2: int}
     */
    private function getRequestParams(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $tagSlugs = collect(Arr::wrap($request->query('tags', [])))
            ->map(fn($slug) => trim((string) $slug))
            ->filter();

        if ($tagSlugs->isEmpty() && $request->filled('tag')) {
            $fallback = trim((string) $request->query('tag', ''));
            if ($fallback !== '') {
                $tagSlugs = collect([$fallback]);
            }
        }

        $page = max(1, (int) $request->query('page', 1));

        return [$search, $tagSlugs, $page];
    }

    private function getRedirectResponse(Request $request, ?Category $category, string $search, Collection $tagSlugs, int $page): ?RedirectResponse
    {
        $normalizedQuery = $this->normalizedQueryPayload($search, $tagSlugs, $page);
        if ($this->shouldRedirectToNormalizedQuery($request, $normalizedQuery)) {
            $routeName = $category ? 'rent.category' : 'rent.discover';
            $routeParams = $category ? ['category_slug' => $category->slug] : [];
            return redirect()->route($routeName, array_merge($routeParams, $normalizedQuery));
        }

        return null;
    }

    private function fetchChildCategories(Category $category): Collection
    {
        $category->loadMissing(['parent', 'media']);

        return Category::with('media')
            ->whereType('rental')
            ->where('parent_id', $category->getKey())
            ->orderBy('name')
            ->get()
            ->each(fn($c) => $c->setRelation('parent', $category));
    }

    private function fetchRentProducts(?Category $category, Collection $childCategories, string $search, Collection $tagSlugs, int $page)
    {
        $query = RentProduct::query()
            ->with(['tags', 'media']);

        if ($category) {
            $categoryIds = $childCategories
                ->pluck('id')
                ->prepend($category->getKey());

            $query->whereIn('category_id', $categoryIds->all());
        } else {
            $query->with(['category.parent']);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($tagSlugs->isNotEmpty()) {
            $query->whereHas('tags', function ($tagQuery) use ($tagSlugs) {
                $tagQuery->where(function ($inner) use ($tagSlugs) {
                    foreach ($tagSlugs as $tagSlug) {
                        $inner->orWhere('slug->vi', $tagSlug)
                            ->orWhere('slug->en', $tagSlug)
                            ->orWhere('slug', $tagSlug)
                            ->orWhere('name->vi', $tagSlug)
                            ->orWhere('name->en', $tagSlug)
                            ->orWhere('name', $tagSlug);
                    }
                });
            });
        }

        $rentProducts = $query
            ->orderByDesc('created_at')
            ->paginate(self::DISCOVER_PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        if ($category) {
            $relatedCategories = $childCategories->concat([$category])->keyBy('id');
            $rentProducts->getCollection()->each(function (RentProduct $product) use ($relatedCategories) {
                if ($cat = $relatedCategories->get($product->category_id)) {
                    $product->setRelation('category', $cat);
                }
            });
        }

        return $rentProducts;
    }

    private function fetchSidebarCategories(?Category $category): Collection
    {
        $categories = Cache::remember(
            CacheKey::RENT_DISCOVER_CATEGORIES_SIDEBAR->value,
            now()->addhours(4),
            fn() => Category::whereType('rental')
                ->with('media')
                ->orderBy('name')
                ->limit(15)
                ->get()
        );

        if ($category) {
            $categories->each(function (Category $cat) use ($category) {
                if ($cat->parent_id === $category->id) {
                    $cat->setRelation('parent', $category);
                }
            });
        }

        $categories->loadMissing('parent');

        return $categories;
    }

    private function fetchSidebarTags(): Collection
    {
        return Cache::remember(
            CacheKey::RENT_DISCOVER_TAGS_SIDEBAR->value,
            now()->addHours(4),
            fn() => Taggable::getModelTags('RentProduct')
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedQueryPayload(string $search, Collection $tagSlugs, int $page): array
    {
        $query = [];

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($tagSlugs->isNotEmpty()) {
            $query['tags'] = $tagSlugs->values()->all();
        }

        if ($page > 1) {
            $query['page'] = $page;
        }

        return $query;
    }

    private function shouldRedirectToNormalizedQuery(Request $request, array $normalizedQuery): bool
    {
        $currentQuery = $request->query();

        if (array_key_exists('tag', $currentQuery)) {
            return true;
        }

        if (isset($currentQuery['tags']) && !is_array($currentQuery['tags'])) {
            return true;
        }

        $currentTags = [];
        if (isset($currentQuery['tags']) && is_array($currentQuery['tags'])) {
            $currentTags = collect($currentQuery['tags'])
                ->map(fn($tag) => trim((string) $tag))
                ->filter()
                ->values()
                ->all();

            if (count($currentTags) !== count($currentQuery['tags'])) {
                return true;
            }
        }

        if (isset($currentQuery['q']) && trim((string) $currentQuery['q']) === '') {
            return true;
        }

        $currentPage = (int) ($currentQuery['page'] ?? 1);
        if ($currentPage <= 1 && array_key_exists('page', $currentQuery)) {
            return true;
        }

        $sanitizedCurrent = [];
        if (isset($currentQuery['q']) && trim((string) $currentQuery['q']) !== '') {
            $sanitizedCurrent['q'] = trim((string) $currentQuery['q']);
        }

        if (!empty($currentTags)) {
            $sanitizedCurrent['tags'] = $currentTags;
        }

        if ($currentPage > 1) {
            $sanitizedCurrent['page'] = $currentPage;
        }

        ksort($sanitizedCurrent);
        $expected = $normalizedQuery;
        ksort($expected);

        return $sanitizedCurrent !== $expected;
    }

    /**
     * @return array{id:int,url:string,thumbnail:string|null}[]
     */
    private function buildPreviewImages(RentProduct $rentProduct): array
    {
        return $rentProduct
            ->getMedia('thumbnails')
            ->map(function (Media $media) {
                try {
                    $url = $media->getUrl('thumb');
                } catch (\Throwable $e) {
                    $url = $media->getFullUrl();
                }

                return [
                    'id' => $media->id,
                    'url' => $url,
                    'thumbnail' => $url,
                ];
            })
            ->values()
            ->all();
    }
}
