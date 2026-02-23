<?php

namespace App\Http\Controllers\Api;

use App\Enum\CategoryType;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogDetailResource;
use App\Http\Resources\Api\BlogResource;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Models\EventOrganizationGuide;
use App\Models\GoodLocation;
use App\Models\Location;
use App\Models\VocationalKnowledge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BlogController extends Controller
{
    use PaginatesApi;

    private const BLOGS_PER_PAGE = 9;

    /**
     * GET /api/blog/category
     *
     * Query: blog_type
     * Response: { categories }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function category(Request $request)
    {
        $blogType = $request->validate([
            'blog_type' => 'required|string',
        ])['blog_type'];

        $categoryType = $this->resolveCategoryType($blogType);
        if (!$categoryType) {
            return response()->json(['message' => 'Invalid blog type.'], 422);
        }

        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', $categoryType)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * GET /api/blog/search
     *
     * Query: blog_type, q, category_slug, page
     * Extra filters for good_location: province_id, district_id, max_people, location_detail
     * Response: { blogs, categories, category, filters, locations? }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'blog_type' => 'required|string',
            'q' => 'nullable|string',
            'category_slug' => 'nullable|string',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'max_people' => 'nullable|integer',
            'location_detail' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        $blogType = $validated['blog_type'];
        $categoryType = $this->resolveCategoryType($blogType);
        if (!$categoryType) {
            return response()->json(['message' => 'Invalid blog type.'], 422);
        }

        $page = max(1, (int) ($validated['page'] ?? 1));
        $search = trim((string) ($validated['q'] ?? ''));

        $category = null;
        if (!empty($validated['category_slug'])) {
            $category = Category::query()
                ->select(['id', 'name', 'slug', 'parent_id', 'description'])
                ->with(['parent:id,name,slug'])
                ->where('slug', $validated['category_slug'])
                ->where('type', $categoryType)
                ->first();
        }

        if ($categoryType === CategoryType::GOOD_LOCATION->value) {
            $filters = [
                'search' => $search !== '' ? $search : null,
                'province_id' => $this->normalizeId($validated['province_id'] ?? null),
                'district_id' => $this->normalizeId($validated['district_id'] ?? null),
                'max_people' => $this->normalizeId($validated['max_people'] ?? null),
                'location_detail' => trim((string) ($validated['location_detail'] ?? '')) ?: null,
            ];

            $query = $this->buildGoodLocationQuery($filters, $category);
        } else {
            $query = $this->buildSimpleBlogQuery($categoryType, $search, $category);
        }

        $blogs = $query
            ->orderBy('order', 'asc')
            ->orderByDesc('created_at')
            ->paginate(self::BLOGS_PER_PAGE, ['*'], 'page', $page);

        $categories = $this->loadCategories($categoryType);

        $payload = [
            'blogs' => $this->paginatedData($blogs, BlogResource::class),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve() : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'category_slug' => $category?->slug,
            ],
        ];

        if ($categoryType === CategoryType::GOOD_LOCATION->value) {
            $payload['filters'] = array_merge($payload['filters'], [
                'province_id' => $filters['province_id'],
                'district_id' => $filters['district_id'],
                'max_people' => $filters['max_people'],
                'location_detail' => $filters['location_detail'],
            ]);
            $payload['locations'] = [
                'provinces' => $this->loadProvinces(),
            ];
        }

        return response()->json($payload);
    }

    /**
     * GET /api/blog/detail
     *
     * Query: blog_type, category_slug, blog_slug
     * Response: { blog, related }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        $validated = $request->validate([
            'blog_type' => 'required|string',
            'category_slug' => 'required|string',
            'blog_slug' => 'required|string',
        ]);

        $blogType = $validated['blog_type'];
        $categoryType = $this->resolveCategoryType($blogType);
        if (!$categoryType) {
            return response()->json(['message' => 'Invalid blog type.'], 422);
        }

        [$blog, $related] = $this->loadBlogDetail($categoryType, $validated['category_slug'], $validated['blog_slug']);

        return response()->json([
            'blog' => BlogDetailResource::make($blog)->resolve($request),
            'related' => BlogResource::collection($related)->resolve(),
        ]);
    }

    private function resolveCategoryType(string $blogType): ?string
    {
        return match ($blogType) {
            CategoryType::GOOD_LOCATION->value => CategoryType::GOOD_LOCATION->value,
            CategoryType::EVENT_ORGANIZATION_GUIDE->value => CategoryType::EVENT_ORGANIZATION_GUIDE->value,
            CategoryType::VOCATIONAL_KNOWLEDGE->value => CategoryType::VOCATIONAL_KNOWLEDGE->value,
            default => null,
        };
    }

    private function buildGoodLocationQuery(array $filters, ?Category $category): Builder
    {
        $query = GoodLocation::query()
            ->select([
                'id',
                'category_id',
                'user_id',
                'title',
                'slug',
                'content',
                'video_url',
                'location_id',
                'latitude',
                'longitude',
                'address',
                'max_people',
                'type',
                'order',
                'created_at',
                'updated_at',
            ])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
                'location:id,name,parent_id,type',
                'location.province:id,name,parent_id',
            ])
            ->where('type', CategoryType::GOOD_LOCATION->value);

        if ($category) {
            $categoryIds = $this->categoryAndDescendantIds($category);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($filters['search']) {
            $query->where(function ($builder) use ($filters) {
                $builder->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('slug', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('content', 'like', '%' . $filters['search'] . '%');
            });
        }

        $this->applyLocationFilter($query, $filters['province_id'], $filters['district_id']);
        $this->applyLocationDetailFilter($query, $filters['location_detail']);
        $this->applyMaxPeopleFilter($query, $filters['max_people']);

        return $query;
    }

    private function buildSimpleBlogQuery(string $categoryType, string $search, ?Category $category): Builder
    {
        $model = match ($categoryType) {
            CategoryType::EVENT_ORGANIZATION_GUIDE->value => EventOrganizationGuide::class,
            CategoryType::VOCATIONAL_KNOWLEDGE->value => VocationalKnowledge::class,
            default => EventOrganizationGuide::class,
        };

        $query = $model::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'order', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
            ])
            ->where('type', $categoryType);

        if ($category) {
            $categoryIds = $this->categoryAndDescendantIds($category);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function loadCategories(string $categoryType): Collection
    {
        return Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', $categoryType)
            ->orderBy('order', 'asc')
            ->get();
    }

    private function loadProvinces(): Collection
    {
        return Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{0:\Illuminate\Database\Eloquent\Model,1:\Illuminate\Support\Collection}
     */
    private function loadBlogDetail(string $categoryType, string $categorySlug, string $blogSlug): array
    {
        if ($categoryType === CategoryType::GOOD_LOCATION->value) {
            $blog = GoodLocation::query()
                ->select([
                    'id',
                    'category_id',
                    'user_id',
                    'title',
                    'slug',
                    'content',
                    'video_url',
                    'location_id',
                    'latitude',
                    'longitude',
                    'address',
                    'max_people',
                    'type',
                    'order',
                    'created_at',
                    'updated_at',
                ])
                ->with([
                    'category:id,name,slug,parent_id',
                    'category.parent:id,name,slug',
                    'author:id,name',
                    'media',
                    'tags',
                    'location:id,name,parent_id,type',
                    'location.province:id,name,parent_id',
                ])
                ->where('type', $categoryType)
                ->whereHas('category', fn ($builder) => $builder
                    ->where('slug', $categorySlug)
                    ->where('type', $categoryType))
                ->where('slug', $blogSlug)
                ->firstOrFail();

            $related = GoodLocation::query()
                ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at'])
                ->with([
                    'category:id,name,slug,parent_id',
                    'category.parent:id,name,slug',
                    'author:id,name',
                    'media',
                ])
                ->where('type', $categoryType)
                ->where('category_id', $blog->category_id)
                ->whereKeyNot($blog->getKey())
                ->latest('created_at')
                ->take(6)
                ->get();

            return [$blog, $related];
        }

        if ($categoryType === CategoryType::EVENT_ORGANIZATION_GUIDE->value) {
            $blog = EventOrganizationGuide::query()
                ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at', 'updated_at'])
                ->with([
                    'category:id,name,slug,parent_id',
                    'category.parent:id,name,slug',
                    'author:id,name',
                    'media',
                    'tags',
                ])
                ->where('type', $categoryType)
                ->whereHas('category', fn ($builder) => $builder
                    ->where('slug', $categorySlug)
                    ->where('type', $categoryType))
                ->where('slug', $blogSlug)
                ->firstOrFail();

            $related = EventOrganizationGuide::query()
                ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at'])
                ->with([
                    'category:id,name,slug,parent_id',
                    'category.parent:id,name,slug',
                    'author:id,name',
                    'media',
                ])
                ->where('type', $categoryType)
                ->where('category_id', $blog->category_id)
                ->whereKeyNot($blog->getKey())
                ->latest('created_at')
                ->take(6)
                ->get();

            return [$blog, $related];
        }

        $blog = VocationalKnowledge::query()
            ->select([
                'id',
                'category_id',
                'user_id',
                'title',
                'slug',
                'content',
                'video_url',
                'location_id',
                'latitude',
                'longitude',
                'address',
                'max_people',
                'type',
                'order',
                'created_at',
                'updated_at',
            ])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
                'location:id,name,parent_id,type',
                'location.province:id,name,parent_id',
            ])
            ->where('type', $categoryType)
            ->whereHas('category', fn ($builder) => $builder
                ->where('slug', $categorySlug)
                ->where('type', $categoryType))
            ->where('slug', $blogSlug)
            ->firstOrFail();

        $related = VocationalKnowledge::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
            ])
            ->where('type', $categoryType)
            ->where('category_id', $blog->category_id)
            ->whereKeyNot($blog->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        return [$blog, $related];
    }

    private function categoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->getKey()];

        $children = Category::query()
            ->select(['id'])
            ->where('parent_id', $category->getKey())
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($ids, $children)));
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $intValue = (int) $value;
            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    private function applyLocationFilter(Builder $query, ?int $provinceId, ?int $districtId): void
    {
        $locationIds = $this->resolveLocationFilter($provinceId, $districtId);
        if ($locationIds !== null) {
            $query->whereIn('location_id', $locationIds);
        }
    }

    private function applyLocationDetailFilter(Builder $query, ?string $locationDetail): void
    {
        if ($locationDetail === null || trim($locationDetail) === '') {
            return;
        }

        $term = trim($locationDetail);
        $escaped = addcslashes($term, '%_');

        $query->where('address', 'like', '%' . $escaped . '%');
    }

    private function applyMaxPeopleFilter(Builder $query, ?int $maxPeople): void
    {
        [$min, $max] = $this->maxPeopleRange($maxPeople);

        if ($min !== null) {
            $query->where('max_people', '>=', $min);
        }
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function maxPeopleRange(?int $value): array
    {
        if ($value === null) {
            return [null, null];
        }

        return match ($value) {
            50 => [null, 50],
            100 => [50, 100],
            200 => [100, 200],
            500 => [200, 500],
            1000 => [500, null],
            default => [null, $value],
        };
    }

    private function resolveLocationFilter(?int $provinceId, ?int $districtId): ?array
    {
        if ($districtId) {
            return [$districtId];
        }

        if ($provinceId) {
            $ids = Location::query()
                ->select('id')
                ->where(function ($builder) use ($provinceId) {
                    $builder->where('id', $provinceId)
                        ->orWhere('parent_id', $provinceId);
                })
                ->pluck('id')
                ->all();

            if (empty($ids)) {
                return [$provinceId];
            }

            return array_unique(array_merge([$provinceId], $ids));
        }

        return null;
    }
}
