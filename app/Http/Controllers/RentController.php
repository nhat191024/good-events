<?php

namespace App\Http\Controllers;

use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\RentProductResource;
use App\Http\Resources\Home\TagResource;
use App\Models\Category;
use App\Models\RentProduct;
use App\Models\Taggable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
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
        $category = Category::query()
            ->with('parent')
            ->where('slug', $categorySlug)
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function rentDetail(Request $request, string $categorySlug, string $rentProductSlug): Response
    {
        $rentProduct = RentProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug))
            ->where('slug', $rentProductSlug)
            ->firstOrFail();

        $previewImages = $this->buildPreviewImages($rentProduct);

        $related = RentProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->where('category_id', $rentProduct->category_id)
            ->whereKeyNot($rentProduct->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

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
        $search = trim((string) $request->query('q', ''));
        $tagSlugs = collect(Arr::wrap($request->query('tags', [])))
            ->map(fn ($slug) => trim((string) $slug))
            ->filter();

        if ($tagSlugs->isEmpty() && $request->filled('tag')) {
            $fallback = trim((string) $request->query('tag', ''));
            if ($fallback !== '') {
                $tagSlugs = collect([$fallback]);
            }
        }

        $page = max(1, (int) $request->query('page', 1));

        $normalizedQuery = $this->normalizedQueryPayload($search, $tagSlugs, $page);
        if ($this->shouldRedirectToNormalizedQuery($request, $normalizedQuery)) {
            $routeName = $category ? 'rent.category' : 'rent.discover';
            $routeParams = $category ? ['category_slug' => $category->slug] : [];
            return redirect()->route($routeName, array_merge($routeParams, $normalizedQuery));
        }

        $query = RentProduct::query()
            ->with(['category.parent', 'tags', 'media']);

        $categoryIds = new Collection();
        if ($category) {
            $category = $category->loadMissing('parent');
            $categoryIds = Category::query()
                ->where('parent_id', $category->getKey())
                ->pluck('id')
                ->prepend($category->getKey());

            $query->whereIn('category_id', $categoryIds->all());
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

        $categories = Category::query()
            ->with('parent', 'media')
            ->orderBy('name')
            ->limit(15)
            ->get();

        $tags = Taggable::getModelTags('RentProduct');

        return Inertia::render('rent/Discover', [
            'rentProducts' => RentProductResource::collection($rentProducts),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'tags' => $tagSlugs->values()->all(),
            ],
        ]);
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
                ->map(fn ($tag) => trim((string) $tag))
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
        $expireAt = now()->addDay();

        return $rentProduct
            ->getMedia('thumbnails')
            ->map(function (Media $media) use ($expireAt) {
                try {
                    $url = $media->getTemporaryUrl($expireAt);
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
