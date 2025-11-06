<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Http\Resources\AssetOrder\AssetOrderResource;
use App\Models\FileProductBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

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

        $status = $bill->status instanceof FileProductBillStatus
            ? $bill->status
            : FileProductBillStatus::tryFrom((string) $bill->status);

        if ($status !== FileProductBillStatus::PENDING) {
            return response()->json([
                'message' => __('Đơn hàng đã được thanh toán hoặc không thể thanh toán lại.'),
            ], 422);
        }

        $bill->forceFill([
            'status' => FileProductBillStatus::PAID,
            'final_total' => $bill->final_total ?? $bill->total,
        ])->save();

        $bill->refresh()->load(['fileProduct.media', 'fileProduct.category']);

        return response()->json([
            'order' => AssetOrderResource::make($bill),
        ]);
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
