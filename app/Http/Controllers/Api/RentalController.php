<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\RentProductResource;
use App\Http\Resources\Api\TagResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\RentProduct;
use App\Models\RentalCategory;
use App\Models\Taggable;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RentalController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    /**
     * GET /api/rental/home
     *
     * Query: page, per_page
     * Response: { rent_products, tags, categories, settings }
     *
     * @param Request $request
     * @param AppSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request, AppSettings $settings)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $rentProducts = RentProduct::with('category.parent', 'category', 'tags', 'media')
            ->paginate($perPage, ['*'], 'page', $page);

        $tags = Taggable::getModelTags('RentProduct');
        $categories = RentalCategory::limit(15)
            ->where('type', 'rental')
            ->orderBy('order', 'asc')
            ->whereParentId(null)
            ->whereIsShow(1)
            ->with('media')
            ->get();

        return response()->json([
            'rent_products' => $this->paginatedData($rentProducts, RentProductResource::class),
            'tags' => TagResource::collection($tags),
            'categories' => CategoryResource::collection($categories),
            'settings' => [
                'app_name' => $settings->app_name,
                'hero_title' => $settings->app_rental_title,
                'banner_images' => $this->bannerImages('rental'),
            ],
        ]);
    }

    /**
     * GET /api/rental/search
     *
     * Query: q, tags[], tag (fallback), category_slug, page, per_page
     * Response: { rent_products, categories, tags, category, child_categories, filters }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
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

        $category = null;
        if ($request->filled('category_slug')) {
            $category = Category::query()
                ->with('parent')
                ->where('slug', $request->query('category_slug'))
                ->first();
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, 12);

        $query = RentProduct::query()
            ->with(['category.parent', 'tags', 'media']);

        $childCategories = new Collection();
        $categoryIds = new Collection();
        if ($category) {
            $category = $category->loadMissing('parent');
            $childCategories = Category::query()
                ->with('parent', 'media')
                ->where('parent_id', $category->getKey())
                ->orderBy('name')
                ->get();
            $categoryIds = $childCategories
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
            ->paginate($perPage, ['*'], 'page', $page);

        $categories = Category::query()
            ->with('parent', 'media')
            ->orderBy('name')
            ->limit(15)
            ->get();

        $tags = Taggable::getModelTags('RentProduct');

        return response()->json([
            'rent_products' => $this->paginatedData($rentProducts, RentProductResource::class),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve() : null,
            'child_categories' => CategoryResource::collection($childCategories),
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'tags' => $tagSlugs->values()->all(),
                'category_slug' => $category?->slug,
            ],
        ]);
    }

    /**
     * GET /api/rental/detail/{categorySlug}/{rentProductSlug}
     *
     * Response: { rent_product, related, contact_hotline }
     *
     * @param Request $request
     * @param string $categorySlug
     * @param string $rentProductSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, string $categorySlug, string $rentProductSlug)
    {
        $rentProduct = RentProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug))
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

        $related->each(fn ($product) => $product->setRelation('category', $rentProduct->category));

        $rentProductPayload = array_merge(
            RentProductResource::make($rentProduct)->resolve(),
            [
                'long_description' => $rentProduct->description,
                'highlights' => [],
                'preview_images' => $previewImages,
                'tags' => TagResource::collection($rentProduct->tags)->resolve(),
                'updated_human' => optional($rentProduct->updated_at)->diffForHumans(),
            ]
        );

        return response()->json([
            'rent_product' => $rentProductPayload,
            'related' => RentProductResource::collection($related)->resolve(),
            'contact_hotline' => config('services.rent.hotline', '0901 234 567'),
        ]);
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
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

    /**
     * @return array<int, string>
     */
    private function bannerImages(string $type): array
    {
        $banner = Banner::where('type', $type)->first();
        if (!$banner) {
            return [];
        }

        return $banner->getMedia('banners')
            ->map(function ($media) {
                try {
                    $url = $media->getUrl('thumb');
                } catch (\Throwable $e) {
                    $url = $media->getFullUrl();
                }

                return $url ?: null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
