<?php

namespace App\Http\Controllers\Blog;

use App\Http\Resources\Home\BlogResource;
use App\Http\Resources\Home\CategoryResource;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VocationalKnowledgeController extends BaseBlogPageController
{
    private const BLOG_TYPE = 'vocational_knowledge';

    public function index(Request $request): Response
    {
        return $this->renderKnowledgePage($request, null);
    }

    public function category(Request $request, string $categorySlug): Response
    {
        $category = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('slug', $categorySlug)
            ->where('type', self::BLOG_TYPE)
            ->firstOrFail();

        return $this->renderKnowledgePage($request, $category);
    }

    private function renderKnowledgePage(Request $request, ?Category $category = null): Response
    {
        $search = trim((string) $request->query('q', ''));

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

        $blogs = $this->paginateBlogs($query, $request);

        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'description'])
            ->with(['parent:id,name,slug'])
            ->where('type', self::BLOG_TYPE)
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('blog/Knowledge', [
            'blogs' => BlogResource::collection($blogs),
            'categories' => CategoryResource::collection($categories),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
            ],
        ]);
    }
}
