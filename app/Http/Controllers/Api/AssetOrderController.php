<?php

namespace App\Http\Controllers\Api;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FileProductBillResource;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssetOrderController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $orders = FileProductBill::query()
            ->where('client_id', $userId)
            ->with(['fileProduct.media', 'fileProduct.category'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'orders' => $this->paginatedData($orders, FileProductBillResource::class),
        ]);
    }

    public function show(Request $request, FileProductBill $bill): JsonResponse
    {
        $this->authorizeBill($bill, $request);

        return response()->json([
            'order' => FileProductBillResource::make(
                $bill->loadMissing(['fileProduct.media', 'fileProduct.category'])
            )->resolve(),
        ]);
    }

    public function repay(Request $request, FileProductBill $bill, PaymentService $paymentService): JsonResponse
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PENDING) {
            return response()->json([
                'message' => 'Order already paid or cannot be repaid.',
            ], 422);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => 'File product not found.',
            ], 500);
        }

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
                    'checkout_url' => $paymentResponse['checkoutUrl'],
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => 'Unable to start payment. Please try again.',
        ], 500);
    }

    public function download(Request $request, FileProductBill $bill)
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PAID) {
            return response()->json([
                'message' => 'Order not paid.',
            ], 422);
        }

        $key = 'downloads_weekly:bill:' . $bill->getKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Download limit reached.',
                'available_in' => $seconds,
            ], 429);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => 'File product not found.',
            ], 500);
        }

        $medias = $fileProduct->getMedia('designs');
        if ($medias->isEmpty()) {
            return response()->json([
                'message' => 'No files to download.',
            ], 404);
        }

        RateLimiter::hit($key, 60 * 60 * 24 * 7);

        $zipFileName = sprintf('FPB-%s-designs.zip', $bill->getKey());

        if (!class_exists('\ZipStream\ZipStream')) {
            return response()->json([
                'message' => 'ZipStream not installed.',
            ], 500);
        }

        $response = new StreamedResponse(function () use ($medias, $zipFileName) {
            $zipStreamClass = '\ZipStream\ZipStream';
            $zip = new $zipStreamClass(outputName: $zipFileName, sendHttpHeaders: false, enableZip64: true);

            foreach ($medias as $media) {
                try {
                    $disk = $media->disk;
                    $path = $media->getPath();
                    try {
                        $diskRoot = Storage::disk($disk)->path('');
                        if (!empty($diskRoot) && Str::startsWith($path, $diskRoot)) {
                            $path = ltrim(Str::after($path, $diskRoot), '/\\');
                        }
                    } catch (Throwable $ex) {
                        // ignore
                    }

                    $fileStream = Storage::disk($disk)->readStream($path);
                    if (!$fileStream) {
                        continue;
                    }

                    $fileName = $media->file_name;
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $storeExtensions = ['psd', 'psb', 'tif'];
                    $isStore = in_array($ext, $storeExtensions, true) || ($media->size && $media->size > 50 * 1024 * 1024);

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
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $zipFileName . '"');

        return $response;
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }

    private function authorizeBill(FileProductBill $bill, Request $request): void
    {
        $userId = $request->user()?->getAuthIdentifier();

        if (!$userId || $bill->client_id !== $userId) {
            abort(403, 'Not authorized.');
        }
    }
}
