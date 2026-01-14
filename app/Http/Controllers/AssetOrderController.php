<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Http\Resources\AssetOrder\AssetOrderResource;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use Filament\Support\Assets\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetOrderController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): Response
    {
        return Inertia::render('asset/orders/Index', [
            'orders' => Inertia::lazy(fn() => $this->getOrders($request)),
            'activeOrder' => Inertia::lazy(fn() => $this->getActiveOrder($request)),
            'statusOptions' => collect(FileProductBillStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    public function show(Request $request, FileProductBill $bill): AssetOrderResource
    {
        $this->authorizeBill($bill, $request);

        return AssetOrderResource::make(
            $bill->loadMissing(['fileProduct.media', 'fileProduct.category'])
        );
    }

    public function repay(Request $request, FileProductBill $bill): JsonResponse
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PENDING) {
            return response()->json([
                'message' => __('Đơn hàng đã được thanh toán hoặc không thể thanh toán lại.'),
            ], 422);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => __('Không tìm thấy thông tin sản phẩm cho đơn hàng.'),
            ], 500);
        }

        $paymentService = app(PaymentService::class);
        $amount = (int) round($bill->final_total ?? $bill->total);
        $orderCode = $bill->getKey() . time();
        $tax = 0.1 * $fileProduct->price;

        $payload = [
            'billId' => $orderCode,
            'billCode' => 'FPB-' . $bill->getKey(),
            'amount' => $amount,
            'buyerName' => $request->user()?->name,
            'buyerEmail' => $request->user()?->email,
            'buyerPhone' => $request->user()?->phone,
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
            $channel = $bill->payment_method instanceof PaymentMethod
                ? $bill->payment_method->gatewayChannel()
                : PaymentMethod::QR_TRANSFER->gatewayChannel();

            $paymentResponse = $paymentService->processAppointmentPayment(
                $payload,
                $channel,
                false,
                $returnUrl,
                $returnUrl
            );

            if (isset($paymentResponse['checkoutUrl'])) {
                return response()->json([
                    'checkoutUrl' => $paymentResponse['checkoutUrl'],
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => __('Không thể khởi tạo lại thanh toán, vui lòng thử lại sau.'),
        ], 500);
    }

    public function downloadZip(Request $request, FileProductBill $bill)
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PAID) {
            return view('error', [
                'message' => __('Đơn hàng chưa thanh toán hoặc không thể tải xuống.'),
            ]);
        }

        $key = 'downloads_weekly:bill:' . $bill->getKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $availableAt = now()->addSeconds($seconds)->format('H:i d/m/Y');

            return view('error', [
                'message' => __('Bạn đã vượt quá giới hạn tải xuống (5 lần/tuần). Vui lòng thử lại vào lúc :time.', ['time' => $availableAt]),
            ]);
        }

        $bill->loadMissing('fileProduct.files');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => __('Không tìm thấy thông tin sản phẩm cho đơn hàng.'),
            ], 500);
        }

        $files = $fileProduct->files;
        if ($files->isEmpty()) {
            return response()->json([
                'message' => __('Không có tệp để tải xuống.'),
            ], 404);
        }

        // Calculate current files hash to check for changes
        $currentHash = $this->calculateFilesHash($files);

        // Check if we have a valid cached zip
        if ($this->hasValidCachedZip($fileProduct, $currentHash)) {
            RateLimiter::hit($key, 60 * 60 * 24 * 7);

            // Return cached zip from S3
            return redirect()->away(Storage::disk('s3')->temporaryUrl(
                $fileProduct->cached_zip_path,
                now()->addMinutes(5)
            ));
        }

        // Delete old cached zip if exists
        if ($fileProduct->cached_zip_path) {
            Storage::disk('s3')->delete($fileProduct->cached_zip_path);
        }

        RateLimiter::hit($key, 60 * 60 * 24 * 7);

        // Generate new zip and cache it
        return $this->generateAndCacheZip($fileProduct, $files, $currentHash, $bill);
    }

    private function calculateFilesHash($files): string
    {
        $hashData = $files->map(function ($file) {
            return $file->id . '|' . $file->updated_at->timestamp . '|' . $file->size;
        })->implode('::');

        return md5($hashData);
    }

    private function hasValidCachedZip($fileProduct, string $currentHash): bool
    {
        if (!$fileProduct->cached_zip_path || !$fileProduct->cached_zip_hash) {
            return false;
        }

        // Check if hash matches (no files changed)
        if ($fileProduct->cached_zip_hash !== $currentHash) {
            return false;
        }

        // Check if cached zip file exists on S3
        if (!Storage::disk('s3')->exists($fileProduct->cached_zip_path)) {
            return false;
        }

        return true;
    }

    private function generateAndCacheZip($fileProduct, $files, string $hash, $bill)
    {
        if (!class_exists('\ZipStream\\ZipStream')) {
            return response()->json([
                'message' => 'ZipStream not installed. Please composer require maennchen/zipstream-php',
            ], 500);
        }

        // Create temporary file to store zip
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip_');
        $zipFileName = sprintf('FPB-%s-designs.zip', $bill->getKey());
        $s3ZipPath = 'cached-zips/' . $fileProduct->id . '/' . $zipFileName;

        try {
            // Create zip file
            $zipStreamClass = '\\ZipStream\\ZipStream';
            $outputStream = fopen($tempZipPath, 'w');

            $zip = new $zipStreamClass(
                outputStream: $outputStream,
                sendHttpHeaders: false,
                enableZip64: true
            );

            foreach ($files as $file) {
                try {
                    $disk = $file->disk;
                    $path = str_replace('\\', '/', $file->path);

                    $fileStream = Storage::disk($disk)->readStream($path);
                    if (!$fileStream) {
                        continue;
                    }

                    $fileName = $file->file_name;
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $storeExtensions = ['psd', 'psb', 'tif'];
                    $isStore = in_array($ext, $storeExtensions, true) || ($file->size && $file->size > 50 * 1024 * 1024);

                    $compressionMethod = $isStore ? \ZipStream\CompressionMethod::STORE : null;
                    $deflateLevel = $isStore ? 0 : null;

                    $zip->addFileFromStream($fileName, $fileStream, '', $compressionMethod, $deflateLevel);

                    if (is_resource($fileStream)) {
                        fclose($fileStream);
                    }
                } catch (Throwable $ex) {
                    report($ex);
                }
            }

            $zip->finish();
            fclose($outputStream);

            // Upload to S3
            Storage::disk('s3')->put(
                $s3ZipPath,
                file_get_contents($tempZipPath)
            );

            // Update file product with cached zip info
            $fileProduct->update([
                'cached_zip_path' => $s3ZipPath,
                'cached_zip_generated_at' => now(),
                'cached_zip_hash' => $hash,
            ]);

            // Clean up temp file
            @unlink($tempZipPath);

            // Redirect to S3 URL
            return redirect()->away(Storage::disk('s3')->temporaryUrl(
                $s3ZipPath,
                now()->addMinutes(5)
            ));

        } catch (Throwable $ex) {
            // Clean up temp file on error
            @unlink($tempZipPath);
            report($ex);

            return response()->json([
                'message' => __('Không thể tạo file zip. Vui lòng thử lại sau.'),
            ], 500);
        }
    }

    private function getOrders(Request $request): AnonymousResourceCollection|array
    {
        $userId = $request->user()?->getAuthIdentifier();
        if (!$userId) {
            return [];
        }

        $page = max(1, (int) $request->query('page', 1));

        $orders = FileProductBill::query()
            ->where('client_id', $userId)
            ->with(['fileProduct.media', 'fileProduct.category'])
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        return AssetOrderResource::collection($orders);
    }

    private function getActiveOrder(Request $request): ?AssetOrderResource
    {

        $orderId = (int) $request->query('bill_id');
        if (!$orderId) {
            return null;
        }

        $userId = $request->user()?->getAuthIdentifier();
        if (!$userId) {
            return null;
        }

        $bill = FileProductBill::query()
            ->where('client_id', $userId)
            ->whereKey($orderId)
            ->with(['fileProduct.media', 'fileProduct.category'])
            ->first();

        return $bill ? AssetOrderResource::make($bill) : null;
    }

    private function authorizeBill(FileProductBill $bill, Request $request): void
    {
        $userId = $request->user()?->getAuthIdentifier();

        if (!$userId || $bill->client_id !== $userId) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        }
    }
}
