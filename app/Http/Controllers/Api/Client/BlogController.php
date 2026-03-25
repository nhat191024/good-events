<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;

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
    public function home(Request $request)
    {
        $latestBlogs = Blog::latest()->take(10)->get();
        $typeIds = $latestBlogs->groupBy('type')->map->pluck('id');
        
        $loadedBlogs = collect();
        if ($typeIds->has('vocational_knowledge')) {
            $loadedBlogs = $loadedBlogs->merge(\App\Models\VocationalKnowledge::with('media')->whereIn('id', $typeIds->get('vocational_knowledge'))->get());
        }
        if ($typeIds->has('event_organization_guide')) {
            $loadedBlogs = $loadedBlogs->merge(\App\Models\EventOrganizationGuide::with('media')->whereIn('id', $typeIds->get('event_organization_guide'))->get());
        }
        if ($typeIds->has('good_location')) {
            $loadedBlogs = $loadedBlogs->merge(\App\Models\GoodLocation::with('media')->whereIn('id', $typeIds->get('good_location'))->get());
        }
        
        $blogs = $latestBlogs->map(function ($blog) use ($loadedBlogs) {
            return $loadedBlogs->firstWhere('id', $blog->id) ?? $blog;
        });

        // get 10 latest posts for each categories
        $payload = [
            'blogs' => BlogResource::collection($blogs),
        ];

        return response()->json($payload);
    }

}
