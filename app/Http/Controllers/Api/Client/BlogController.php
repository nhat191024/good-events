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
        // get 10 latest posts for each categories
        $payload = [
            'blogs' => BlogResource::collection(Blog::latest()->take(10)->get()),
        ];

        return response()->json($payload);
    }

}
