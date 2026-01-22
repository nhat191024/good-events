<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Inertia\Response as InertiaResponse;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Enum\CacheKey;

use App\Models\Category;
use App\Models\FileProduct;
use App\Models\FileProductBill;
use App\Models\Taggable;

use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\FileProductResource;
use App\Http\Resources\Home\TagResource;

use App\Helper\TemporaryImage;
use App\Services\PaymentService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

use Throwable;
use function activity;

class FileProductController extends Controller
{
    private const DISCOVER_PER_PAGE = 12;
    private const SUGGESTION_LIMIT = 10;

    public function assetDiscover(Request $request): Response|RedirectResponse
    {
        return $this->renderDiscoverPage($request, null);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));
        if ($term === '') {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = FileProduct::query()
            ->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            })
            ->orderByDesc('created_at')
            ->limit(self::SUGGESTION_LIMIT * 3)
            ->pluck('name')
            ->filter()
            ->unique()
            ->take(self::SUGGESTION_LIMIT)
            ->values();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function assetCategory(Request $request, string $category_slug): Response|RedirectResponse
    {
        $category = Category::with('parent')
            ->where('type', 'design')
            ->where('slug', $category_slug)
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function assetDetail(Request $request, string $category_slug, string $file_product_slug): Response
    {
        $category = $this->fetchCachedCategory($category_slug);
        $fileProduct = $this->fetchFileProduct($category, $file_product_slug);

        $related = $this->fetchRelatedProducts($fileProduct, $category);
        $downloadCount = $this->getDownloadCount($fileProduct);

        [$isPurchased, $downloadZipUrl] = $this->checkPurchaseStatus($request->user(), $fileProduct);

        return Inertia::render('asset/Detail', [
            'fileProduct' => $this->buildFileProductPayload($request, $fileProduct, $downloadCount),
            'related' => FileProductResource::collection($related)->resolve($request),
            'downloadZipUrl' => $downloadZipUrl,
            'isPurchased' => $isPurchased,
        ]);
    }

    private function fetchCachedCategory(string $slug): Category
    {
        return Cache::remember(
            CacheKey::FILE_CATEGORY_DETAIL->value . ":{$slug}",
            now()->addHours(4),
            fn() => Category::query()
                ->with(['parent', 'media'])
                ->where('slug', $slug)
                ->firstOrFail()
        );
    }

    private function fetchFileProduct(Category $category, string $slug): FileProduct
    {
        $fileProduct = FileProduct::query()
            ->with(['tags', 'media'])
            ->where('category_id', $category->getKey())
            ->where('slug', $slug)
            ->firstOrFail();

        $fileProduct->setRelation('category', $category);

        return $fileProduct;
    }

    private function fetchRelatedProducts(FileProduct $fileProduct, Category $category): Collection
    {
        $related = FileProduct::query()
            ->with(['tags', 'media'])
            ->where('category_id', $fileProduct->category_id)
            ->whereKeyNot($fileProduct->getKey())
            ->latest('created_at')
            ->take(6)
            ->get();

        $related->each(fn($product) => $product->setRelation('category', $category));

        return $related;
    }

    private function getDownloadCount(FileProduct $fileProduct): int
    {
        return FileProductBill::query()
            ->where('file_product_id', $fileProduct->getKey())
            ->where('status', FileProductBillStatus::PAID->value)
            ->count();
    }

    /**
     * @return array{0: bool, 1: string|null}
     */
    private function checkPurchaseStatus(?\App\Models\User $user, FileProduct $fileProduct): array
    {
        if (!$user) {
            return [false, null];
        }

        $bill = FileProductBill::query()
            ->where('file_product_id', $fileProduct->getKey())
            ->where('client_id', $user->getAuthIdentifier())
            ->where('status', FileProductBillStatus::PAID->value)
            ->first();

        if ($bill) {
            return [true, route('client-orders.asset.downloadZip', ['bill' => $bill->getKey()])];
        }

        return [false, null];
    }

    private function buildFileProductPayload(Request $request, FileProduct $fileProduct, int $downloadCount): array
    {
        $previewImages = $this->buildPreviewImages($fileProduct);

        return array_merge(
            FileProductResource::make($fileProduct)->resolve($request),
            [
                'long_description' => $fileProduct->description,
                'highlights' => [],
                'preview_images' => $previewImages,
                'included_files' => [],
                'tags' => TagResource::collection($fileProduct->tags)->resolve($request),
                'download_count' => $downloadCount,
                'updated_human' => optional($fileProduct->updated_at)->diffForHumans(),
            ]
        );
    }

    public function assetPurchase(Request $request, string $slug): Response|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
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

        $sessionBuyer = $request->session()->pull('asset_purchase_form', []);
        $buyer = array_merge($buyerDefaults, $sessionBuyer);

        $paymentMethods = PaymentMethod::toOptions();
        if (!$buyer['payment_method']) {
            $buyer['payment_method'] = PaymentMethod::QR_TRANSFER->value;
        }

        return Inertia::render('asset/Purchase', [
            'fileProduct' => FileProductResource::make($fileProduct)->resolve($request),
            'buyer' => $buyer,
            'paymentMethods' => $paymentMethods,
            'totals' => $this->calculateTotals((float) $fileProduct->price),
        ]);
    }

    public function assetConfirmPurchase(Request $request): RedirectResponse|InertiaResponse|HttpResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $this->validatePurchaseRequest($request);
        $this->logPurchaseDebug('validated input', ['user_id' => $user->getAuthIdentifier(), 'input' => Arr::except($validated, ['email'])]);

        $fileProduct = $this->fetchPurchaseProduct($validated['slug']);

        $this->updateBuyerProfile($user, $validated);

        $bill = $this->createPurchaseBill($user, $fileProduct, $validated);
        $this->logPurchaseDebug('bill created/updated', ['bill_id' => $bill->getKey(), 'total' => $bill->final_total]);

        $this->logPurchaseActivity($user, $bill, $validated);

        $request->session()->flash('asset_purchase_form', Arr::except($validated, ['slug']));

        return $this->processPurchasePayment($bill, $fileProduct, $validated);
    }

    private function validatePurchaseRequest(Request $request): array
    {
        $allowedMethods = array_map(fn(PaymentMethod $m) => $m->value, PaymentMethod::cases());

        return $request->validate([
            'slug' => ['required', 'string', 'exists:file_products,slug'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'tax_code' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $allowedMethods)],
        ]);
    }

    private function fetchPurchaseProduct(string $slug): FileProduct
    {
        return FileProduct::query()
            ->with(['category'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function updateBuyerProfile(\App\Models\User $user, array $validated): void
    {
        $user->fill([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        if ($user->isDirty()) {
            $user->save();
        }
    }

    private function createPurchaseBill(\App\Models\User $user, FileProduct $fileProduct, array $validated): FileProductBill
    {
        $tax = 0.1 * $fileProduct->price;
        return FileProductBill::updateOrCreate(
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
    }

    private function logPurchaseActivity(\App\Models\User $user, FileProductBill $bill, array $validated): void
    {
        activity('file_product_checkout')
            ->performedOn($bill)
            ->causedBy($user)
            ->withProperties(Arr::only($validated, [
                'name', 'email', 'phone', 'company', 'tax_code', 'note', 'payment_method',
            ]))
            ->log('Asset purchase request submitted');
    }

    private function processPurchasePayment(FileProductBill $bill, FileProduct $fileProduct, array $validated): RedirectResponse|InertiaResponse|HttpResponse
    {
        $paymentService = app(PaymentService::class);
        $payload = $this->buildPaymentPayload($bill, $fileProduct, $validated);

        $this->logPurchaseDebug('payment payload prepared', [
            'bill_id' => $bill->getKey(),
            'payment_payload' => Arr::except($payload, ['buyerEmail', 'buyerPhone']),
        ]);

        $returnUrl = route('payment.result', ['bill_id' => $bill->getKey()]);

        try {
            $paymentResponse = $paymentService->processAppointmentPayment(
                $payload,
                PaymentMethod::from($validated['payment_method'])->gatewayChannel(),
                false,
                $returnUrl,
                $returnUrl
            );

            $this->logPurchaseDebug('payment response', ['bill_id' => $bill->getKey(), 'response' => $paymentResponse]);

            if (isset($paymentResponse['checkoutUrl'])) {
                return Inertia::location($paymentResponse['checkoutUrl']);
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->logPurchaseDebug('payment exception', ['bill_id' => $bill->getKey(), 'error' => $exception->getMessage()]);
        }

        return redirect()
            ->route('asset.buy', ['slug' => $fileProduct->slug])
            ->with('error', 'Không thể khởi tạo thanh toán. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
    }

    private function buildPaymentPayload(FileProductBill $bill, FileProduct $fileProduct, array $validated): array
    {
        $tax = 0.1 * $fileProduct->price;
        $billTotal = (int) round($bill->final_total ?? $fileProduct->price);

        return [
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
    }

    private function logPurchaseDebug(string $message, array $context = []): void
    {
        \Illuminate\Support\Facades\Log::debug("assetConfirmPurchase - {$message}", $context);
    }

    private function renderDiscoverPage(Request $request, ?Category $category = null): Response|RedirectResponse
    {
        $params = $this->getDiscoverPageParams($request);

        if ($redirect = $this->handleDiscoverRedirect($request, $params, $category)) {
            return $redirect;
        }

        $childCategories = $this->getChildCategories($category);

        $fileProducts = $this->getFileProducts(
            $params['search'],
            $params['tagSlugs'],
            $params['page'],
            $category,
            $childCategories
        );

        [$categories, $tags] = $this->getSidebarData();

        return Inertia::render('asset/Discover', [
            'fileProducts' => FileProductResource::collection($fileProducts),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'childCategories' => CategoryResource::collection($childCategories),
            'filters' => [
                'q' => $params['search'] !== '' ? $params['search'] : null,
                'tags' => $params['tagSlugs']->values()->all(),
            ],
        ]);
    }

    /**
     * @return array{search: string, tagSlugs: Collection, page: int}
     */
    private function getDiscoverPageParams(Request $request): array
    {
        $search = trim((string) $request->query('q', ''));
        $tagSlugs = collect(Arr::wrap($request->query('tags', [])))
            ->map(fn($slug) => trim((string) $slug))
            ->filter();

        // Fallback for singular 'tag' parameter
        if ($tagSlugs->isEmpty() && $request->filled('tag')) {
            $fallback = trim((string) $request->query('tag', ''));
            if ($fallback !== '') {
                $tagSlugs = collect([$fallback]);
            }
        }

        $page = max(1, $request->integer('page', 1));

        return compact('search', 'tagSlugs', 'page');
    }

    private function handleDiscoverRedirect(Request $request, array $params, ?Category $category): ?RedirectResponse
    {
        $normalizedQuery = $this->normalizedQueryPayload($params['search'], $params['tagSlugs'], $params['page']);

        if ($this->shouldRedirectToNormalizedQuery($request, $normalizedQuery)) {
            $routeName = $category ? 'asset.category' : 'asset.discover';
            $routeParams = $category ? ['category_slug' => $category->slug] : [];
            return redirect()->route($routeName, array_merge($routeParams, $normalizedQuery));
        }

        return null;
    }

    private function getChildCategories(?Category $category): Collection
    {
        if (!$category) {
            return new Collection();
        }

        $category->loadMissing('parent');

        $children = Category::with('media')
            ->where('parent_id', $category->getKey())
            ->orderBy('name')
            ->get();

        $children->each(fn($child) => $child->setRelation('parent', $category));

        return $children;
    }

    private function getFileProducts(string $search, Collection $tagSlugs, int $page, ?Category $category, Collection $childCategories)
    {
        return FileProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->when($category, function ($q) use ($category, $childCategories) {
                $ids = $childCategories->pluck('id')->push($category->getKey());
                $q->whereIn('category_id', $ids);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($tagSlugs->isNotEmpty(), function ($q) use ($tagSlugs) {
                $q->whereHas('tags', function ($tagQuery) use ($tagSlugs) {
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
            })
            ->orderByDesc('created_at')
            ->paginate(self::DISCOVER_PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();
    }

    private function getSidebarData(): array
    {
        $categories = Cache::remember(CacheKey::FILE_DISCOVER_CATEGORIES_SIDEBAR->value, now()->addMinutes(10), fn() =>
            Category::with('parent', 'media')
                ->whereType('design')
                ->orderBy('name')
                ->limit(15)
                ->get()
        );

        $tags = Cache::remember(CacheKey::FILE_DISCOVER_TAGS_SIDEBAR->value, now()->addMinutes(10), fn() =>
            Taggable::getModelTags('FileProduct')
        );

        return [$categories, $tags];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizedQueryPayload(string $search, Collection $tagSlugs, int $page): array
    {
        $query = [];

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($tagSlugs->isNotEmpty()) {
            $query['tags'] = $tagSlugs->values()->all();
        }

        if ($page > 1) {
            $query['page'] = $page;
        }

        return $query;
    }

    private function shouldRedirectToNormalizedQuery(Request $request, array $normalizedQuery): bool
    {
        $currentQuery = $request->query();

        if (array_key_exists('tag', $currentQuery)) {
            return true;
        }

        if (isset($currentQuery['tags']) && !is_array($currentQuery['tags'])) {
            return true;
        }

        $currentTags = [];
        if (isset($currentQuery['tags']) && is_array($currentQuery['tags'])) {
            $currentTags = collect($currentQuery['tags'])
                ->map(fn($tag) => trim((string) $tag))
                ->filter()
                ->values()
                ->all();

            if (count($currentTags) !== count($currentQuery['tags'])) {
                return true;
            }
        }

        if (isset($currentQuery['q']) && trim((string) $currentQuery['q']) === '') {
            return true;
        }

        $currentPage = (int) ($currentQuery['page'] ?? 1);
        if ($currentPage <= 1 && array_key_exists('page', $currentQuery)) {
            return true;
        }

        $sanitizedCurrent = [];
        if (isset($currentQuery['q']) && trim((string) $currentQuery['q']) !== '') {
            $sanitizedCurrent['q'] = trim((string) $currentQuery['q']);
        }

        if (!empty($currentTags)) {
            $sanitizedCurrent['tags'] = $currentTags;
        }

        if ($currentPage > 1) {
            $sanitizedCurrent['page'] = $currentPage;
        }

        ksort($sanitizedCurrent);
        $expected = $normalizedQuery;
        ksort($expected);

        return $sanitizedCurrent !== $expected;
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
}
