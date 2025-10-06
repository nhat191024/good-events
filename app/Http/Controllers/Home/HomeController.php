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
        // $rootCategories = PartnerCategory::whereNull('parent_id')
        //     ->orderBy('name')
        //     ->get();

        // Get event categories (children of "Sự kiện" category)
        // $eventCategory =
        $eventCategories = PartnerCategory::whereNull('parent_id')->orderBy("name")->get();
        $partnerCategories = [];

        if ($eventCategories->count() > 0) {
            // $eventCategories = PartnerCategory::where('parent_id', $eventCategory->id)
            //     ->orderBy('name')
            //     ->get();

            // Get partner categories for each event category
            $expireAt = now()->addMinutes(5);
            foreach ($eventCategories as $category) {
                $partnerCategories[$category->id] = PartnerCategory::where('parent_id', $category->id)
                    ->orderBy('min_price')
                    ->limit(8)
                    ->get()
                    ->map(function ($pc) use ($expireAt) {
                        return [
                            'id' => $pc->id,
                            'name' => $pc->name,
                            'slug' => $pc->slug,
                            'description' => $pc->description,
                            'min_price' => $pc->min_price,
                            'max_price' => $pc->max_price,
                            'image' => $pc->getFirstTemporaryUrl($expireAt, 'images')
                        ];
                    });
            }
        }
        // dd( $partnerCategories, $eventCategories);
        return Inertia::render('home/Home', [
            // 'rootCategories' => $rootCategories,
            'eventCategories' => $eventCategories,
            'partnerCategories' => $partnerCategories,
        ]);
    }
}
