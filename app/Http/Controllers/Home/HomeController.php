<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index(): Response
    {
        // Get root categories (parent_id is null)
        $rootCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Get event categories (children of "Sự kiện" category)
        $eventCategory = Category::where('slug', 'su-kien')->first();
        $eventCategories = [];
        $partnerCategories = [];

        if ($eventCategory) {
            $eventCategories = Category::where('parent_id', $eventCategory->id)
                ->orderBy('name')
                ->get();

            // Get partner categories for each event category
            foreach ($eventCategories as $category) {
                $partnerCategories[$category->id] = PartnerCategory::where('category_id', $category->id)
                    ->orderBy('min_price')
                    ->limit(8) // Limit for performance
                    ->get();
            }
        }

        return Inertia::render('home/Home', [
            'rootCategories' => $rootCategories,
            'eventCategories' => $eventCategories,
            'partnerCategories' => $partnerCategories,
        ]);
    }
}
