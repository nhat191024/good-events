<?php

namespace App\Http\Controllers\Blog;

use App\Enum\CategoryType;
use App\Http\Resources\Home\BlogDetailResource;
use App\Http\Resources\Home\BlogResource;
use App\Http\Resources\Home\CategoryResource;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventOrganizationGuideController extends BaseBlogPageController
{
    private const BLOG_TYPE = CategoryType::EVENT_ORGANIZATION_GUIDE->value;

    public function index(Request $request): Response
    {
        return $this->renderGuidePage($request, null);
    }

    public function category(Request $request, string $categorySlug): Response
    {
        $category = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('slug', $categorySlug)
            ->where('type', self::BLOG_TYPE)
            ->firstOrFail();

        return $this->renderGuidePage($request, $category);
    }

    public function show(Request $request, string $categorySlug, string $blogSlug): Response
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
            ->whereHas('category', fn($builder) => $builder
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

    private function renderGuidePage(Request $request, ?Category $category = null): Response
    {
        $search = trim((string) $request->query('q', ''));

        $query = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'video_url', 'order', 'created_at'])
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

        $blogs = $this->paginateBlogs($query, $request);

        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', self::BLOG_TYPE)
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('blog/Guide', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
            ],
        ]);
    }

    private function detailContext(): array
    {
        return [
            'breadcrumbLabel' => 'Blog hướng dẫn',
            'discoverRouteName' => 'blog.guides.discover',
            'categoryRouteName' => 'blog.guides.category',
            'detailRouteName' => 'blog.guides.show',
            'pageTitleSuffix' => 'Blog hướng dẫn Sukientot',
        ];
    }
}
