<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RentHomeController extends Controller
{
    public const RECORD_PER_PAGE = 20;

    /**
     * Show the home page.
     */
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));

        // $fileProducts = FileProduct::with('category.parent', 'category', 'tags')
        //     ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        // $tags = Taggable::getModelTags('FileProduct');
        // $categories = Category::limit(15)->get();

        return Inertia::render('home/AssetHome', [
            // 'fileProducts' => FileProductResource::collection($fileProducts),
            // 'tags' => TagResource::collection($tags),
            // 'categories' => CategoryResource::collection($categories),
        ]);
    }
}
