<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\RentProductResource;
use App\Http\Resources\Home\TagResource;
use App\Models\Category;
use App\Models\RentProduct;
use App\Models\Taggable;
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

        $rentProducts = RentProduct::with('category.parent', 'category', 'tags', 'media')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        $tags = Taggable::getModelTags('RentProduct');
        $categories = Category::limit(15)->get();

        return Inertia::render('home/RentHome', [
            'rentProducts' => RentProductResource::collection($rentProducts),
            'tags' => TagResource::collection($tags),
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}
