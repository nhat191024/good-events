<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\PartnerCategory;
use App\Settings\AppSettings;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index(AppSettings $settings): Response
    {
        $expireAt = now()->addMinutes(60);

        // Eager load event categories with their children and media
        $eventCategories = PartnerCategory::whereNull('parent_id')
            ->with([
                'media',
                'children' => function ($query) {
                    $query->orderBy('order', 'asc')
                        ->limit(8)
                        ->with('media');
                },
            ])
            ->orderBy('order', 'asc')
            ->get();

        // Transform partner categories
        $partnerCategories = [];
        foreach ($eventCategories as $category) {
            $partnerCategories[$category->id] = $category->children->map(function ($pc) use ($expireAt) {
                return [
                    'id' => $pc->id,
                    'name' => $pc->name,
                    'slug' => $pc->slug,
                    'description' => $pc->description,
                    'min_price' => $pc->min_price,
                    'max_price' => $pc->max_price,
                    'image' => $this->getTemporaryImageUrl($pc, $expireAt),
                ];
            });
        }

        return Inertia::render('home/Home', [
            'eventCategories' => $eventCategories,
            'partnerCategories' => $partnerCategories,
            'settings' => [
                'app_name' => $settings->app_name,
                'app_partner_banner' => $settings->app_partner_banner,
            ],
        ]);
    }

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (! method_exists($model, 'getFirstTemporaryUrl')) {
            return null;
        }

        try {
            return $model->getFirstTemporaryUrl($expireAt, 'images');
        } catch (\Throwable $e) {
            return method_exists($model, 'getFirstMediaUrl')
                ? $model->getFirstMediaUrl('images')
                : null;
        }
    }
}
