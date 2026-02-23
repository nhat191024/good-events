<?php

namespace App\Http\Controllers\Api;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\FileProductResource;
use App\Http\Resources\Api\TagResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\DesignCategory;
use App\Models\FileProduct;
use App\Models\FileProductBill;
use App\Models\Taggable;
use App\Services\PaymentService;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class AssetController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    /**
     * GET /api/asset/home
     *
     * Query: page, per_page
     * Response: { file_products, tags, categories, settings }
     *
     * @param Request $request
     * @param AppSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request, AppSettings $settings)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $fileProducts = FileProduct::with('category.parent', 'category', 'tags', 'media')
            ->paginate($perPage, ['*'], 'page', $page);

        $tags = Taggable::getModelTags('FileProduct');
        $categories = DesignCategory::limit(15)
            ->where('type', 'design')
            ->orderBy('order', 'asc')
            ->whereParentId(null)
            ->whereIsShow(1)
            ->with('media')
            ->get();

        return response()->json([
            'file_products' => $this->paginatedData($fileProducts, FileProductResource::class),
            'tags' => TagResource::collection($tags),
            'categories' => CategoryResource::collection($categories),
            'settings' => [
                'app_name' => $settings->app_name,
                'hero_title' => $settings->app_design_title,
                'banner_images' => $this->bannerImages('design'),
            ],
        ]);
    }

    /**
     * GET /api/asset/search
     *
     * Query: q, tags[], tag (fallback), category_slug, page, per_page
     * Response: { file_products, categories, tags, category, child_categories, filters }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $tagSlugs = collect(Arr::wrap($request->query('tags', [])))
            ->map(fn ($slug) => trim((string) $slug))
            ->filter();

        if ($tagSlugs->isEmpty() && $request->filled('tag')) {
            $fallback = trim((string) $request->query('tag', ''));
            if ($fallback !== '') {
                $tagSlugs = collect([$fallback]);
            }
        }

        $category = null;
        if ($request->filled('category_slug')) {
            $category = Category::query()
                ->with('parent')
                ->where('slug', $request->query('category_slug'))
                ->first();
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, 12);

        $query = FileProduct::query()
            ->with(['category.parent', 'tags', 'media']);

        $childCategories = new Collection();
        $categoryIds = new Collection();
        if ($category) {
            $category = $category->loadMissing('parent');
            $childCategories = Category::query()
                ->with('parent', 'media')
                ->where('parent_id', $category->getKey())
                ->orderBy('name')
                ->get();
            $categoryIds = $childCategories
                ->pluck('id')
                ->prepend($category->getKey());

            $query->whereIn('category_id', $categoryIds->all());
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($tagSlugs->isNotEmpty()) {
            $query->whereHas('tags', function ($tagQuery) use ($tagSlugs) {
                $tagQuery->where(function ($inner) use ($tagSlugs) {
                    foreach ($tagSlugs as $tagSlug) {
                        $inner->orWhere('slug->vi', $tagSlug)
                            ->orWhere('slug->en', $tagSlug)
                            ->orWhere('slug', $tagSlug)
                            ->orWhere('name->vi', $tagSlug)
                            ->orWhere('name->en', $tagSlug)
                            ->orWhere('name', $tagSlug);
                    }
                });
            });
        }

        $fileProducts = $query
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $categories = Category::query()
            ->with('parent', 'media')
            ->orderBy('name')
            ->limit(15)
            ->get();

        $tags = Taggable::getModelTags('FileProduct');

        return response()->json([
            'file_products' => $this->paginatedData($fileProducts, FileProductResource::class),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve() : null,
            'child_categories' => CategoryResource::collection($childCategories),
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'tags' => $tagSlugs->values()->all(),
                'category_slug' => $category?->slug,
            ],
        ]);
    }

    /**
     * GET /api/asset/detail/{categorySlug}/{fileProductSlug}
     *
     * Response: { file_product, related, download_zip_url, is_purchased }
     *
     * @param Request $request
     * @param string $categorySlug
     * @param string $fileProductSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, string $categorySlug, string $fileProductSlug)
    {
        $fileProduct = FileProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->whereHas('category', fn ($builder) => $builder->where('slug', $categorySlug))
            ->where('slug', $fileProductSlug)
            ->firstOrFail();

        $previewImages = $this->buildPreviewImages($fileProduct);

        $related = FileProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->where('category_id', $fileProduct->category_id)
            ->whereKeyNot($fileProduct->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        $downloadCount = FileProductBill::query()
            ->where('file_product_id', $fileProduct->getKey())
            ->where('status', FileProductBillStatus::PAID->value)
            ->count();

        $user = $request->user();
        $isPurchased = false;
        $downloadZipUrl = null;

        if ($user) {
            $isPurchased = FileProductBill::query()
                ->where('file_product_id', $fileProduct->getKey())
                ->where('client_id', $user->getAuthIdentifier())
                ->where('status', FileProductBillStatus::PAID->value)
                ->exists();

            if ($isPurchased) {
                $bill = FileProductBill::query()
                    ->where('file_product_id', $fileProduct->getKey())
                    ->where('client_id', $user->getAuthIdentifier())
                    ->where('status', FileProductBillStatus::PAID->value)
                    ->first();
                if ($bill) {
                    $downloadZipUrl = route('api.asset-orders.download', ['bill' => $bill->getKey()]);
                }
            }
        }

        $fileProductPayload = array_merge(
            FileProductResource::make($fileProduct)->resolve(),
            [
                'long_description' => $fileProduct->description,
                'highlights' => [],
                'preview_images' => $previewImages,
                'included_files' => [],
                'tags' => TagResource::collection($fileProduct->tags)->resolve(),
                'download_count' => $downloadCount,
                'updated_human' => optional($fileProduct->updated_at)->diffForHumans(),
            ]
        );

        return response()->json([
            'file_product' => $fileProductPayload,
            'related' => FileProductResource::collection($related)->resolve(),
            'download_zip_url' => $downloadZipUrl,
            'is_purchased' => $isPurchased,
        ]);
    }

    /**
     * GET /api/asset/purchase/{slug}
     *
     * Response: { file_product, buyer, payment_methods, totals }
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request, string $slug)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $fileProduct = FileProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $buyerDefaults = [
            'name' => $user->name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'company' => null,
            'tax_code' => null,
            'note' => null,
            'payment_method' => null,
        ];

        $paymentMethods = PaymentMethod::toOptions();
        $buyerDefaults['payment_method'] = $buyerDefaults['payment_method'] ?? PaymentMethod::QR_TRANSFER->value;

        return response()->json([
            'file_product' => FileProductResource::make($fileProduct)->resolve(),
            'buyer' => $buyerDefaults,
            'payment_methods' => $paymentMethods,
            'totals' => $this->calculateTotals((float) $fileProduct->price),
        ]);
    }

    /**
     * POST /api/asset/purchase
     *
     * Body: slug, name, email, phone, company, tax_code, note, payment_method
     * Response: { checkout_url, bill_id } (500 with message on failure)
     *
     * @param Request $request
     * @param PaymentService $paymentService
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPurchase(Request $request, PaymentService $paymentService)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $paymentMethods = PaymentMethod::toOptions();
        $allowedMethods = array_map(fn (PaymentMethod $m) => $m->value, PaymentMethod::cases());

        $validated = $request->validate([
            'slug' => ['required', 'string', 'exists:file_products,slug'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $allowedMethods)],
        ]);

        $fileProduct = FileProduct::query()
            ->with(['category'])
            ->where('slug', $validated['slug'])
            ->firstOrFail();

        $user->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        if ($user->isDirty()) {
            $user->save();
        }

        $tax = 0.1 * $fileProduct->price;
        $bill = FileProductBill::updateOrCreate(
            [
                'file_product_id' => $fileProduct->getKey(),
                'client_id' => $user->getAuthIdentifier(),
                'status' => FileProductBillStatus::PENDING->value,
            ],
            [
                'total' => $fileProduct->price,
                'tax' => $tax,
                'final_total' => $fileProduct->price + $tax,
                'tax_code' => $validated['tax_code'],
                'company' => $validated['company'],
                'note' => $validated['note'],
                'payment_method' => $validated['payment_method'],
            ]
        );

        $billTotal = (int) round($bill->final_total ?? $fileProduct->price);
        $paymentPayload = [
            'billId' => $bill->getKey() . time(),
            'billCode' => 'FPB-' . $bill->getKey(),
            'amount' => $billTotal,
            'buyerName' => $validated['name'],
            'buyerEmail' => $validated['email'],
            'buyerPhone' => $validated['phone'],
            'items' => [
                [
                    'name' => $fileProduct->name,
                    'price' => (int) round($fileProduct->price),
                    'quantity' => 1,
                ],
                [
                    'name' => 'VAT 10%',
                    'price' => (int) round($tax),
                    'quantity' => 1,
                ],
            ],
            'expiryTime' => intval(now()->addMinutes(10)->timestamp),
        ];

        $returnUrl = route('payment.result', ['bill_id' => $bill->getKey()]);

        try {
            $paymentResponse = $paymentService->processAppointmentPayment(
                $paymentPayload,
                PaymentMethod::from($validated['payment_method'])->gatewayChannel(),
                false,
                $returnUrl,
                $returnUrl
            );

            if (isset($paymentResponse['checkoutUrl'])) {
                return response()->json([
                    'checkout_url' => $paymentResponse['checkoutUrl'],
                    'bill_id' => $bill->getKey(),
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => 'Unable to start payment. Please try again.',
        ], 500);
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }

    /**
     * @return array{id:int,url:string,thumbnail:string|null}[]
     */
    private function buildPreviewImages(FileProduct $fileProduct): array
    {
        return $fileProduct
            ->getMedia('thumbnails')
            ->map(function (Media $media) {
                try {
                    $url = $media->getUrl('thumb');
                } catch (\Throwable $e) {
                    $url = $media->getFullUrl();
                }

                return [
                    'id' => $media->id,
                    'url' => $url,
                    'thumbnail' => $url,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{subtotal:float,discount:float,vat:float,total:float}
     */
    private function calculateTotals(float $price): array
    {
        $subtotal = max($price, 0);
        $discount = 0.0;
        $vat = round($subtotal * 0.1, 2);
        $total = max($subtotal - $discount + $vat, 0);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat' => $vat,
            'total' => $total,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function bannerImages(string $type): array
    {
        $banner = Banner::where('type', $type)->first();
        if (!$banner) {
            return [];
        }

        return $banner->getMedia('banners')
            ->map(function ($media) {
                try {
                    $url = $media->getUrl('thumb');
                } catch (\Throwable $e) {
                    $url = $media->getFullUrl();
                }

                return $url ?: null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
