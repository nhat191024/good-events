<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Enum\PaymentMethod;
use App\Http\Resources\AssetOrder\AssetOrderResource;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

    public function download(Request $request, FileProductBill $bill)
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PAID) {
            return response()->json([
                'message' => __('Đơn hàng chưa thanh toán hoặc không thể tải xuống.'),
            ], 403);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => __('Không tìm thấy thông tin sản phẩm cho đơn hàng.'),
            ], 500);
        }

        $mediaId = (int) $request->query('media_id');
        $media = $mediaId
            ? $fileProduct->media()->where('collection_name', 'designs')->whereKey($mediaId)->first()
            : $fileProduct->getFirstMedia('designs');
        if (!$media) {
            return response()->json([
                'message' => __('Không tìm thấy tệp đính kèm.'),
            ], 404);
        }

        try {
            $disk = $media->disk;
            $path = $media->getPath();

            try {
                $diskRoot = Storage::disk($disk)->path('');
                if (!empty($diskRoot) && Str::startsWith($path, $diskRoot)) {
                    $path = ltrim(Str::after($path, $diskRoot), '/\\');
                }
            } catch (Throwable $ex) {
                // Ignore if disk->path() is not supported for this driver
            }

            $stream = Storage::disk($disk)->readStream($path);

            if (!$stream) {
                return response()->json([
                    'message' => __('Không thể đọc tệp. Vui lòng thử lại sau.'),
                ], 500);
            }

            return response()->streamDownload(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, $media->file_name, [
                'Content-Type' => $media->mime_type,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('Không thể tải tệp. Vui lòng thử lại sau.'),
            ], 500);
        }
    }

    public function downloadAll(Request $request, FileProductBill $bill): JsonResponse
    {
        $this->authorizeBill($bill, $request);
        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PAID) {
            return response()->json([
                'message' => __('Đơn hàng chưa thanh toán hoặc không thể tải xuống.'),
            ], 403);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => __('Không tìm thấy thông tin sản phẩm cho đơn hàng.'),
            ], 500);
        }

        $medias = $fileProduct->getMedia('designs');
        $files = [];

        foreach ($medias as $media) {
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

            $url = null;

            if ($disk === 's3') {
                try {
                    $url = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(10));
                } catch (Throwable $ex) {
                    // fallback to server proxy URL
                }
            }

            if (!$url) {
                $url = route('client-orders.asset.download', ['bill' => $bill->getKey()]) . '?media_id=' . $media->id;
            }

            $files[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $url,
            ];
        }

        return response()->json(['files' => $files]);
    }

    public function downloadZip(Request $request, FileProductBill $bill)
    {
        $this->authorizeBill($bill, $request);

        $statusEnum = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($statusEnum !== FileProductBillStatus::PAID) {
            return response()->json([
                'message' => __('Đơn hàng chưa thanh toán hoặc không thể tải xuống.'),
            ], 403);
        }

        $bill->loadMissing('fileProduct');
        $fileProduct = $bill->fileProduct;

        if (!$fileProduct) {
            return response()->json([
                'message' => __('Không tìm thấy thông tin sản phẩm cho đơn hàng.'),
            ], 500);
        }

        $medias = $fileProduct->getMedia('designs');
        if ($medias->isEmpty()) {
            return response()->json([
                'message' => __('Không có tệp để tải xuống.'),
            ], 404);
        }

        $zipFileName = sprintf('FPB-%s-designs.zip', $bill->getKey());

        if (!class_exists('\ZipStream\\ZipStream')) {
            return response()->json([
                'message' => 'ZipStream not installed. Please composer require maennchen/zipstream-php',
            ], 500);
        }

        $response = new StreamedResponse(function () use ($medias, $zipFileName) {
            $zipStreamClass = '\\ZipStream\\ZipStream';

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
                        // skip this file
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
                    // continue with next file
                }
            }

            $zip->finish();
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $zipFileName . '"');

        return $response;
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
