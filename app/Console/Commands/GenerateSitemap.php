<?php

namespace App\Console\Commands;

use App\Enum\CategoryType;
use App\Models\Category;
use App\Models\EventOrganizationGuide;
use App\Models\FileProduct;
use App\Models\GoodLocation;
use App\Models\PartnerCategory;
use App\Models\RentProduct;
use App\Models\VocationalKnowledge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap manually for SEO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sitemap = Sitemap::create();

        // 1. Static Routes
        // Define route names that are public and static
        $staticRoutes = [
            'home',
            'about.index',
            'contact.index',
            'static.faq',
            'static.privacy',
            'static.shipping',
            'static.terms',
            'tutorial.index',
            'tutorial.partner',
            'tutorial.client',
            'asset.home',
            'asset.discover',
            'rent.home',
            'rent.discover',
            'blog.discover',
            'blog.guides.discover',
            'blog.knowledge.discover'
        ];

        foreach ($staticRoutes as $routeName) {
            if (Route::has($routeName)) {
                $sitemap->add(Url::create(route($routeName)));
            }
        }

        // 2. Partner Categories
        PartnerCategory::all()->each(function (PartnerCategory $category) use ($sitemap) {
            $sitemap->add(Url::create(route('partner-categories.show', ['slug' => $category->slug])));
        });

        // 3. File Products (Design)
        // Add Categories first
        Category::where('type', CategoryType::DESIGN)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create(route('asset.category', ['category_slug' => $category->slug])));
        });

        // Add Products
        FileProduct::with('category')->get()->each(function (FileProduct $product) use ($sitemap) {
            if ($product->category) {
                $sitemap->add(Url::create(route('asset.show', [
                    'category_slug' => $product->category->slug,
                    'file_product_slug' => $product->slug
                ])));
            }
        });

        // 4. Rent Products (Rental)
        // Add Categories
        Category::where('type', CategoryType::RENTAL)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create(route('rent.category', ['category_slug' => $category->slug])));
        });

        // Add Products
        RentProduct::with('category')->get()->each(function (RentProduct $product) use ($sitemap) {
            if ($product->category) {
                $sitemap->add(Url::create(route('rent.show', [
                    'category_slug' => $product->category->slug,
                    'rent_product_slug' => $product->slug
                ])));
            }
        });

        // 5. Blogs - Good Location
        // Categories
        Category::where('type', CategoryType::GOOD_LOCATION)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create(route('blog.category', ['category_slug' => $category->slug])));
        });

        // Posts
        GoodLocation::with('category')->get()->each(function (GoodLocation $blog) use ($sitemap) {
            if ($blog->category) {
                $sitemap->add(Url::create(route('blog.show', [
                    'category_slug' => $blog->category->slug,
                    'blog_slug' => $blog->slug
                ])));
            }
        });

        // 6. Blogs - Event Organization Guide
        // Categories
        Category::where('type', CategoryType::EVENT_ORGANIZATION_GUIDE)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create(route('blog.guides.category', ['category_slug' => $category->slug])));
        });

        // Posts
        EventOrganizationGuide::with('category')->get()->each(function (EventOrganizationGuide $blog) use ($sitemap) {
            if ($blog->category) {
                $sitemap->add(Url::create(route('blog.guides.show', [
                    'category_slug' => $blog->category->slug,
                    'blog_slug' => $blog->slug
                ])));
            }
        });

        // 7. Blogs - Vocational Knowledge
        // Categories
        Category::where('type', CategoryType::VOCATIONAL_KNOWLEDGE)->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(Url::create(route('blog.knowledge.category', ['category_slug' => $category->slug])));
        });

        // Posts
        VocationalKnowledge::with('category')->get()->each(function (VocationalKnowledge $blog) use ($sitemap) {
            if ($blog->category) {
                $sitemap->add(Url::create(route('blog.knowledge.show', [
                    'category_slug' => $blog->category->slug,
                    'blog_slug' => $blog->slug
                ])));
            }
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}
