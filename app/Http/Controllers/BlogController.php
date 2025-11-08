<?php

namespace App\Http\Controllers;

use App\Http\Resources\Home\BlogDetailResource;
use App\Http\Resources\Home\BlogResource;
use App\Http\Resources\Home\CategoryResource;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    private const BLOGS_PER_PAGE = 9;

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
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function blogDetail(Request $request, string $categorySlug, string $blogSlug): Response
    {
        $blog = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'created_at', 'updated_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
            ])
            ->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug))
            ->where('slug', $blogSlug)
            ->firstOrFail();

        $related = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
            ])
            ->where('category_id', $blog->category_id)
            ->whereKeyNot($blog->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        return Inertia::render('blog/Detail', [
            'blog' => BlogDetailResource::make($blog)->resolve($request),
            'related' => BlogResource::collection($related),
        ]);
    }

    private function renderDiscoverPage(Request $request, ?Category $category = null): Response
    {
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $query = Blog::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'content', 'created_at'])
            ->with([
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'author:id,name',
                'media',
                'tags',
            ]);

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

        $blogs = $query
            ->orderByDesc('created_at')
            ->paginate(self::BLOGS_PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', 'blog')
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('blog/Discover', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
            ],
        ]);
    }

    /**
     * @return array<int, int>
     */
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
}
