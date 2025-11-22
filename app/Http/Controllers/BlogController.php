<?php

namespace App\Http\Controllers;

use App\Enum\CategoryType;
use App\Http\Controllers\Blog\BaseBlogPageController;
use App\Http\Resources\Home\BlogDetailResource;
use App\Http\Resources\Home\BlogResource;
use App\Http\Resources\Home\CategoryResource;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends BaseBlogPageController
{
    private const BLOG_TYPE = CategoryType::GOOD_LOCATION->value;

    public function blogDiscover(Request $request): Response
    {
        return $this->renderDiscoverPage($request, null);
    }

    public function blogCategory(Request $request, string $categorySlug): Response
    {
        $category = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('slug', $categorySlug)
            ->where('type', self::BLOG_TYPE)
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function blogDetail(Request $request, string $categorySlug, string $blogSlug): Response
    {
        $blog = $this->baseBlogQuery()
            ->whereHas('category', fn ($builder) => $builder
                ->where('slug', $categorySlug)
                ->where('type', self::BLOG_TYPE))
            ->where('slug', $blogSlug)
            ->firstOrFail();

        $related = $this->baseBlogQuery()
            ->where('category_id', $blog->category_id)
            ->whereKeyNot($blog->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        return Inertia::render('blog/Detail', [
            'blog' => BlogDetailResource::make($blog)->resolve($request),
            'related' => BlogResource::collection($related),
            'context' => $this->detailContext(),
        ]);
    }

    private function renderDiscoverPage(Request $request, ?Category $category = null): Response
    {
        $filters = $this->extractFilters($request);
        $blogs = $this->paginateBlogs(
            $this->buildDiscoverQuery($filters, $category),
            $request
        );

        $categories = $this->loadCategories();
        $provinces = $this->loadProvinces();

        return Inertia::render('blog/Discover', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $filters['search'],
                'province_id' => $filters['provinceId'],
                'district_id' => $filters['districtId'],
                'max_people' => $filters['maxPeople'],
            ],
            'locations' => [
                'provinces' => $provinces,
            ],
        ]);
    }

    private function buildDiscoverQuery(array $filters, ?Category $category): Builder
    {
        $query = $this->baseBlogQuery();

        if ($category) {
            $this->applyCategoryFilter($query, $category);
        }

        if ($filters['search'] !== null) {
            $this->applySearchFilter($query, $filters['search']);
        }

        $this->applyLocationFilter($query, $filters['provinceId'], $filters['districtId']);
        $this->applyMaxPeopleFilter($query, $filters['maxPeople']);

        return $query;
    }

    private function baseBlogQuery(): Builder
    {
        return Blog::query()
            ->select([
                'id',
                'category_id',
                'user_id',
                'title',
                'slug',
                'content',
                'video_url',
                'location_id',
                'max_people',
                'type',
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
            ->where('type', self::BLOG_TYPE);
    }

    private function applyCategoryFilter(Builder $query, Category $category): void
    {
        $categoryIds = $this->categoryAndDescendantIds($category);
        $query->whereIn('category_id', $categoryIds);
    }

    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function ($builder) use ($search) {
            $builder->where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('content', 'like', '%' . $search . '%');
        });
    }

    private function applyLocationFilter(Builder $query, ?int $provinceId, ?int $districtId): void
    {
        $locationIds = $this->resolveLocationFilter($provinceId, $districtId);
        if ($locationIds !== null) {
            $query->whereIn('location_id', $locationIds);
        }
    }

    private function applyMaxPeopleFilter(Builder $query, ?int $maxPeople): void
    {
        [$min, $max] = $this->maxPeopleRange($maxPeople);

        if ($min !== null && $max !== null) {
            $query->whereBetween('max_people', [$min, $max]);
            return;
        }

        if ($min !== null) {
            $query->where('max_people', '>=', $min);
            return;
        }

        if ($max !== null) {
            $query->where('max_people', '<=', $max);
        }
    }

    /**
     * Convert the UI capacity filter into a numeric range.
     *
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

    /**
     * Normalize discover filters coming from the request.
     *
     * @return array{
     *     search: string|null,
     *     provinceId: int|null,
     *     districtId: int|null,
     *     maxPeople: int|null,
     * }
     */
    private function extractFilters(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));

        return [
            'search' => $search !== '' ? $search : null,
            'provinceId' => $this->normalizeId($request->query('province_id')),
            'districtId' => $this->normalizeId($request->query('district_id')),
            'maxPeople' => $this->normalizeId($request->query('max_people')),
        ];
    }

    /**
     * @return Collection<int, Category>
     */
    private function loadCategories(): Collection
    {
        return Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', self::BLOG_TYPE)
            ->orderBy('order', 'asc')
            ->get();
    }

    /**
     * @return Collection<int, Location>
     */
    private function loadProvinces(): Collection
    {
        return Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
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

    private function detailContext(): array
    {
        return [
            'breadcrumbLabel' => 'Blog địa điểm',
            'discoverRouteName' => 'blog.discover',
            'categoryRouteName' => 'blog.category',
            'detailRouteName' => 'blog.show',
            'pageTitleSuffix' => 'Blog địa điểm Sukientot',
        ];
    }
}
