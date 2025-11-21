<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Helper\TemporaryImage;
use App\Http\Resources\Home\CategoryResource;
use App\Http\Resources\Home\FileProductResource;
use App\Http\Resources\Home\TagResource;
use App\Models\Category;
use App\Models\FileProduct;
use App\Models\FileProductBill;
use App\Models\Taggable;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Response as InertiaResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

use function activity;

class FileProductController extends Controller
{
    private const DISCOVER_PER_PAGE = 12;

    public function assetDiscover(Request $request): Response|RedirectResponse
    {
        return $this->renderDiscoverPage($request, null);
    }

    public function assetCategory(Request $request, string $category_slug): Response|RedirectResponse
    {
        $category = Category::query()
            ->with('parent')
            ->where('slug', $category_slug)
            ->firstOrFail();

        return $this->renderDiscoverPage($request, $category);
    }

    public function assetDetail(Request $request, string $category_slug, string $file_product_slug): Response
    {
        $fileProduct = FileProduct::query()
            ->with(['category.parent', 'tags', 'media'])
            ->whereHas('category', fn($builder) => $builder->where('slug', $category_slug))
            ->where('slug', $file_product_slug)
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
        $expireAt = now()->addDay();

        $isPurchased = false;
        $downloadUrl = null;

        if ($user) {
            $isPurchased = FileProductBill::query()
                ->where('file_product_id', $fileProduct->getKey())
                ->where('client_id', $user->getAuthIdentifier())
                ->where('status', FileProductBillStatus::PAID->value)
                ->exists();

            if ($isPurchased) {
                $downloadUrl = $this->buildDownloadUrl($fileProduct, $expireAt);
            }
        }

        $fileProductPayload = array_merge(
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

        return Inertia::render('asset/Detail', [
            'fileProduct' => $fileProductPayload,
            'related' => FileProductResource::collection($related)->resolve($request),
            'downloadUrl' => $downloadUrl,
            'isPurchased' => $isPurchased,
        ]);
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

        $paymentMethods = $this->paymentMethods();
        if (!$buyer['payment_method'] && !empty($paymentMethods)) {
            $buyer['payment_method'] = $paymentMethods[0]['code'];
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

        $paymentMethods = $this->paymentMethods();
        $allowedMethods = array_column($paymentMethods, 'code');

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

        \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - validated input', [
            'user_id' => $user->getAuthIdentifier(),
            'input' => Arr::except($validated, ['email']), // avoid logging full email if you prefer; adjust as needed
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

        $bill = FileProductBill::updateOrCreate(
            [
                'file_product_id' => $fileProduct->getKey(),
                'client_id' => $user->getAuthIdentifier(),
                'status' => FileProductBillStatus::PENDING->value,
            ],
            [
                'total' => $fileProduct->price,
                'final_total' => $fileProduct->price,
            ]
        );

        \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - bill created/updated', [
            'bill_id' => $bill->getKey(),
            'file_product_id' => $fileProduct->getKey(),
            'total' => $bill->total,
            'final_total' => $bill->final_total,
        ]);

        activity('file_product_checkout')
            ->performedOn($bill)
            ->causedBy($user)
            ->withProperties(Arr::only($validated, [
                'name',
                'email',
                'phone',
                'company',
                'tax_code',
                'note',
                'payment_method',
            ]))
            ->log('Asset purchase request submitted');

        $request->session()->flash('asset_purchase_form', Arr::except($validated, ['slug']));

        $paymentService = app(PaymentService::class);
        $paymentPayload = [
            'billId' => $bill->getKey() . time(),
            'billCode' => 'FPB-' . $bill->getKey(),
            'amount' => (int) round($bill->final_total ?? $fileProduct->price),
            'buyerName' => $validated['name'],
            'buyerEmail' => $validated['email'],
            'buyerPhone' => $validated['phone'],
            'items' => [
                [
                    'name' => $fileProduct->name,
                    'price' => (int) round($bill->final_total ?? $fileProduct->price),
                    'quantity' => 1,
                ],
            ],
            'expiryTime' => intval(now()->addMinutes(10)->timestamp),
        ];

        \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - payment payload prepared', [
            'bill_id' => $bill->getKey(),
            'payment_payload' => Arr::except($paymentPayload, ['buyerEmail', 'buyerPhone']),
        ]);

        $returnUrl = route('payment.result', ['bill_id' => $bill->getKey()]);

        try {
            $paymentResponse = $paymentService->processAppointmentPayment(
                $paymentPayload,
                'qr_transfer',
                false,
                $returnUrl,
                $returnUrl
            );

            \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - payment response', [
                'bill_id' => $bill->getKey(),
                'response' => is_array($paymentResponse) ? $paymentResponse : (string) $paymentResponse,
            ]);

            if (isset($paymentResponse['checkoutUrl'])) {
                \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - redirecting to checkout', [
                    'checkoutUrl' => $paymentResponse['checkoutUrl'],
                    'bill_id' => $bill->getKey(),
                ]);

                return Inertia::location($paymentResponse['checkoutUrl']);
            }
        } catch (Throwable $exception) {
            report($exception);
            \Illuminate\Support\Facades\Log::debug('assetConfirmPurchase - payment exception', [
                'bill_id' => $bill->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('asset.buy', ['slug' => $fileProduct->slug])
            ->with('error', 'Không thể khởi tạo thanh toán. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
    }

    private function renderDiscoverPage(Request $request, ?Category $category = null): Response|RedirectResponse
    {
        $search = trim((string) $request->query('q', ''));
        $tagSlugs = collect(Arr::wrap($request->query('tags', [])))
            ->map(fn($slug) => trim((string) $slug))
            ->filter();

        if ($tagSlugs->isEmpty() && $request->filled('tag')) {
            $fallback = trim((string) $request->query('tag', ''));
            if ($fallback !== '') {
                $tagSlugs = collect([$fallback]);
            }
        }

        $page = max(1, (int) $request->query('page', 1));

        $normalizedQuery = $this->normalizedQueryPayload($search, $tagSlugs, $page);
        if ($this->shouldRedirectToNormalizedQuery($request, $normalizedQuery)) {
            $routeName = $category ? 'asset.category' : 'asset.discover';
            $routeParams = $category ? ['category_slug' => $category->slug] : [];
            return redirect()->route($routeName, array_merge($routeParams, $normalizedQuery));
        }

        $query = FileProduct::query()
            ->with(['category.parent', 'tags', 'media']);

        $categoryIds = new Collection();
        if ($category) {
            $category = $category->loadMissing('parent');
            $categoryIds = Category::query()
                ->where('parent_id', $category->getKey())
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
            ->paginate(self::DISCOVER_PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        $categories = Category::query()
            ->with('parent', 'media')
            ->orderBy('name')
            ->limit(15)
            ->get();

        $tags = Taggable::getModelTags('FileProduct');

        $view = 'asset/Discover';

        return Inertia::render($view, [
            'fileProducts' => FileProductResource::collection($fileProducts),
            'categories' => CategoryResource::collection($categories),
            'tags' => TagResource::collection($tags),
            'category' => $category ? CategoryResource::make($category)->resolve($request) : null,
            'filters' => [
                'q' => $search !== '' ? $search : null,
                'tags' => $tagSlugs->values()->all(),
            ],
        ]);
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
     * @return array<int, array{code:string,name:string,description:string|null}>
     */
    private function paymentMethods(): array
    {
        return [
            [
                'code' => 'bank_transfer',
                'name' => 'Chuyển khoản ngân hàng',
                'description' => 'Thanh toán qua tài khoản ngân hàng của Sukientot.',
            ],
            [
                'code' => 'cash',
                'name' => 'Tiền mặt',
                'description' => 'Thanh toán trực tiếp tại văn phòng hoặc buổi gặp mặt.',
            ],
            [
                'code' => 'online',
                'name' => 'Thanh toán trực tuyến',
                'description' => 'Thanh toán qua cổng thanh toán điện tử được hỗ trợ.',
            ],
        ];
    }

    /**
     * @return array{id:int,url:string,thumbnail:string|null}[]
     */
    private function buildPreviewImages(FileProduct $fileProduct): array
    {
        $expireAt = now()->addDay();

        return $fileProduct
            ->getMedia('thumbnails')
            ->map(function (Media $media) use ($expireAt) {
                try {
                    $url = $media->getTemporaryUrl($expireAt);
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

    private function buildDownloadUrl(FileProduct $fileProduct, \DateTimeInterface $expireAt): ?string
    {
        try {
            return $fileProduct->getFirstTemporaryUrl($expireAt, 'designs');
        } catch (\Throwable $e) {
            try {
                return $fileProduct->getFirstMediaUrl('designs') ?: TemporaryImage::getTemporaryImageUrl($fileProduct, $expireAt, 'designs');
            } catch (\Throwable $th) {
                return null;
            }
        }
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
