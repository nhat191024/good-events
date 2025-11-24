<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseBlogPageController extends Controller
{
    protected const BLOGS_PER_PAGE = 9;

    /**
     * Paginate the provided query using the shared per-page value.
     */
    protected function paginateBlogs(Builder $query, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));

        return $query
            ->orderBy('order', 'asc')
            ->orderByDesc('created_at')
            ->paginate(static::BLOGS_PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();
    }

    /**
     * Collect the given category id along with its direct children ids.
     *
     * @return array<int, int>
     */
    protected function categoryAndDescendantIds(Category $category): array
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
