<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Blog\BaseBlogPageController;
use App\Http\Resources\Home\BlogDetailResource;
use App\Http\Resources\Home\BlogResource;
use App\Http\Resources\Home\CategoryResource;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends BaseBlogPageController
{
    private const BLOG_TYPE = 'good_location';

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
        $blog = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at', 'updated_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
            ])
            ->where('type', self::BLOG_TYPE)
            ->whereHas('category', fn ($builder) => $builder
                ->where('slug', $categorySlug)
                ->where('type', self::BLOG_TYPE))
            ->where('slug', $blogSlug)
            ->firstOrFail();

        $related = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
            ])
            ->where('type', self::BLOG_TYPE)
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
        $search = trim((string) $request->query('q', ''));
        $provinceId = $this->normalizeId($request->query('province_id'));
        $districtId = $this->normalizeId($request->query('district_id'));

        $query = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
            ])
            ->where('type', self::BLOG_TYPE);

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

        $locationIds = $this->resolveLocationFilter($provinceId, $districtId);
        if ($locationIds !== null) {
            $query->whereIn('location_id', $locationIds);
        }

        $blogs = $this->paginateBlogs($query, $request);

        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', self::BLOG_TYPE)
            ->orderBy('order', 'asc')
            ->get();

        $provinces = Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('blog/Discover', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'province_id' => $provinceId,
                'district_id' => $districtId,
            ],
            'locations' => [
                'provinces' => $provinces,
            ],
        ]);
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
