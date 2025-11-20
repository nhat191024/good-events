<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Http\Resources\AssetOrder\AssetOrderResource;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AssetOrderController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): Response
    {
        return Inertia::render('asset/orders/Index', [
            'orders' => Inertia::lazy(fn () => $this->getOrders($request)),
            'activeOrder' => Inertia::lazy(fn () => $this->getActiveOrder($request)),
            'statusOptions' => collect(FileProductBillStatus::cases())->map(fn ($status) => [
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
                    'price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'expiryTime' => intval(now()->addMinutes(10)->timestamp),
        ];

        $returnUrl = route('payment.result', ['bill_id' => $bill->getKey()]);

        try {
            $paymentResponse = $paymentService->processAppointmentPayment(
                $payload,
                'qr_transfer',
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
