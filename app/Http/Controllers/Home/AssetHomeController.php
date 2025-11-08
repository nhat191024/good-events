<?php

namespace App\Http\Controllers\Home;

use App\Http\Resources\Home\FileProductResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\TagResource;
use App\Models\Category;
use App\Models\FileProduct;
use App\Models\Tag;
use App\Models\Taggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AssetHomeController extends Controller
{
    public const RECORD_PER_PAGE = 20;

    /**
     * Show the home page.
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));

        $fileProducts = FileProduct::with('category.parent', 'category', 'tags', 'media')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        $tags = Taggable::getModelTags('FileProduct');
        $categories = Category::limit(15)
            ->where('type', 'design')
            ->orderBy('order', 'asc')
            ->get();

        return Inertia::render('home/AssetHome', [
            'fileProducts' => FileProductResource::collection($fileProducts),
            'tags' => TagResource::collection($tags),
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}
