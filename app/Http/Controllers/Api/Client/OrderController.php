<?php

namespace App\Http\Controllers\Api\Client;

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillPriceIncreaseRequestStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Order\AcceptPriceIncreaseRequest;
use App\Http\Requests\Client\Order\RejectPriceIncreaseRequest;
use App\Http\Requests\Client\OrderHistory\CancelOrderRequest;
use App\Http\Requests\Client\OrderHistory\ConfirmPartnerRequest;
use App\Http\Resources\Api\PartnerBillDetailResource;
use App\Http\Resources\Api\PartnerBillHistoryResource;
use App\Http\Resources\Api\PartnerBillPriceIncreaseRequestResource;
use App\Http\Resources\Api\PartnerBillResource;
use App\Http\Resources\Api\PartnerProfileResource;
use App\Http\Resources\Api\PartnerServiceResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Partner;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\PartnerBillPriceIncreaseRequest;
use App\Models\Statistical;
use App\Models\User;
use App\Models\Voucher;
use App\Services\FCMService;
use App\Services\PartnerWidgetCacheService;
use Codebyray\ReviewRateable\Models\Review;
use Filament\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;

    private const MAX_PER_PAGE = 50;

    private FCMService $fcmService;

    public function __construct()
    {
        $this->fcmService = app(FCMService::class);
    }

    /**
     * GET /api/orders
     *
     * Query: page, per_page
     * Response: { orders: PartnerBillResource[] (paginated) }
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {

        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $bills = PartnerBill::query()
            ->with([
                'category' => fn ($q) => $q->withTrashed(),
                'category.media',
                'event',
                'details',
                'partner.statistics',
                'partner.partnerProfile',
                'media',
                'voucher' => fn ($q) => $q->select(['id', 'code']),
            ])
            ->where('client_id', $request->user()->id)
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB,
            ])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'orders' => $this->paginatedData($bills, PartnerBillResource::class),
        ]);
    }

    /**
     * GET /api/orders/history
     *
     * Query: page, per_page
     * Response: { orders: PartnerBillHistoryResource[] (paginated) }
     *
     * @return JsonResponse
     */
    public function history(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $bills = PartnerBill::query()
            ->where('client_id', $request->user()->id)
            ->with([
                'media',
                'category' => fn ($q) => $q->withTrashed(),
                'category.media',
                'category.parent' => fn ($q) => $q->withTrashed(),
                'category.parent.media',
                'event',
                'partner.media',
                'partner.statistics',
                'partner.partnerProfile',
                'review' => fn ($query) => $query
                    ->where('reviewable_type', Partner::class)
                    ->where('user_id', $request->user()->id)
                    ->with('ratings'),
                'voucher' => fn ($q) => $q->select(['id', 'code']),
            ])
            ->whereIn('status', [
                PartnerBillStatus::COMPLETED,
                PartnerBillStatus::EXPIRED,
                PartnerBillStatus::CANCELLED,
            ])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'orders' => $this->paginatedData($bills, PartnerBillHistoryResource::class),
        ]);
    }

    /**
     * GET /api/orders/{order}
     *
     * Path: order (id)
     * Response: { order: PartnerBillResource|null }
     *
     * @return JsonResponse
     */
    public function single(Request $request, int $orderId)
    {
        $order = PartnerBill::query()
            ->where('id', $orderId)
            ->where('client_id', $request->user()->id)
            ->with([
                'media',
                'category' => fn ($q) => $q->withTrashed(),
                'category.media',
                'category.parent' => fn ($q) => $q->withTrashed(),
                'category.parent.media',
                'event',
                'details',
                'partner.statistics',
                'partner.partnerProfile',
                'voucher' => fn ($q) => $q->select(['id', 'code']),
            ])
            ->first();

        return response()->json($order ? new PartnerBillResource($order) : null);
    }

    /**
     * GET /api/orders/history/{order}
     *
     * Path: order (id)
     * Response: { order: PartnerBillHistoryResource|null }
     *
     * @return JsonResponse
     */
    public function singleHistory(Request $request, int $orderId)
    {
        $order = PartnerBill::query()
            ->where('id', $orderId)
            ->where('client_id', $request->user()->id)
            ->with([
                'media',
                'category' => fn ($q) => $q->withTrashed(),
                'category.media',
                'category.parent' => fn ($q) => $q->withTrashed(),
                'category.parent.media',
                'event',
                'details',
                'partner.media',
                'partner.statistics',
                'partner.partnerProfile',
                'review' => fn ($query) => $query
                    ->where('reviewable_type', Partner::class)
                    ->where('user_id', $request->user()->id)
                    ->with('ratings'),
                'voucher' => fn ($q) => $q->select(['id', 'code']),
            ])
            ->first();

        return response()->json($order ? new PartnerBillHistoryResource($order) : null);
    }

    /**
     * GET /api/orders/{order}/details
     *
     * Path: order (id)
     * Response: { bill_id, items: PartnerBillDetailResource[], version }
     *
     * @return JsonResponse
     */
    public function details(Request $request, int $billId)
    {
        $details = PartnerBillDetail::query()
            ->where('partner_bill_id', $billId)
            ->with([
                'partner:id,name,avatar',
                'partner.statistics',
                'partner.partnerProfile',
            ])
            ->select(['id', 'partner_bill_id', 'partner_id', 'total', 'status', 'updated_at'])
            ->orderByDesc('id')
            ->get();

        $billUpdatedTs = optional(
            PartnerBill::select(['id', 'updated_at'])->find($billId)
        )->updated_at?->timestamp;

        return response()->json([
            'bill_id' => $billId,
            'items' => PartnerBillDetailResource::collection($details),
            'version' => $billUpdatedTs,
        ]);
    }

    /**
     * GET /api/orders/partner-profile/{user}
     *
     * Path: user (id)
     * Response: { user, partner_profile, services }
     *
     * @return JsonResponse
     */
    public function partnerProfile(User $user)
    {
        $user->loadMissing('partnerProfile', 'partnerServices.category', 'partnerServices.media');

        if (! $user->partnerProfile) {
            return response()->json(['message' => 'Partner profile not found.'], 404);
        }

        return response()->json([
            'user' => new UserResource($user),
            'partner_profile' => new PartnerProfileResource($user->partnerProfile),
            'services' => PartnerServiceResource::collection($user->partnerServices)->resolve(),
        ]);
    }

    /**
     * POST /api/orders/cancel
     *
     * Body: order_id
     * Response: { success: true } or 422 with message
     *
     * @return JsonResponse
     */
    public function cancelOrder(CancelOrderRequest $request)
    {
        $billId = $request->input('order_id');
        Log::debug('[cancelOrder] Request to cancel order', ['bill_id' => $billId]);

        $bill = PartnerBill::findOrFail($billId);
        Log::debug('[cancelOrder] Bill found', ['status' => $bill->status]);

        if ($bill->status != PartnerBillStatus::PENDING) {
            return response()->json(['message' => 'Unable to cancel this order.'], 422);
        }

        if ($bill->date && $bill->start_time) {
            $tz = config('app.timezone') ?: 'UTC';

            try {
                $startDate = $bill->date->format('Y-m-d');
                $startTime = $bill->start_time->format('H:i');
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $startDate.' '.$startTime, $tz);
            } catch (\Throwable $exception) {
                $startDateTime = null;
            }

            if ($startDateTime) {
                $cutoff = $startDateTime->copy()->subHours(8);
                $now = Carbon::now($tz);

                if ($now->greaterThanOrEqualTo($cutoff)) {
                    return response()->json([
                        'message' => 'Cancellation must be at least 8 hours before the event.',
                    ], 422);
                }
            }
        }

        $bill->status = PartnerBillStatus::CANCELLED;
        $bill->save();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/orders/choose-partner
     *
     * Body: order_id, partner_id, voucher_code (optional)
     * Response: { success: true } or error message
     *
     * @return JsonResponse
     */
    public function confirmChoosePartner(ConfirmPartnerRequest $request)
    {
        try {
            $billId = $request->input('order_id');
            $partnerId = $request->input('partner_id');
            $voucherCode = $request->input('voucher_code');

            $bill = PartnerBill::findOrFail($billId);
            $partnerBillDetail = PartnerBillDetail::where('partner_bill_id', $billId)
                ->where('partner_id', $partnerId)
                ->first();

            if (! $partnerBillDetail) {
                return response()->json(['message' => 'Partner selection not found.'], 404);
            }

            $discount = 0;
            $voucher = Voucher::where('code', $voucherCode)->first();
            if ($voucher) {
                $discount = $voucher->getDiscountAmount($partnerBillDetail->total);
            }

            $bill->total = $partnerBillDetail->total;
            $bill->final_total = $partnerBillDetail->total - $discount;
            $bill->partner_id = $partnerBillDetail->partner_id;
            $bill->status = PartnerBillStatus::CONFIRMED;
            $bill->save();

            $partnerBillDetail->status = PartnerBillDetailStatus::CLOSED;
            $partnerBillDetail->save();

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            Log::error('Error in confirming partner', ['exception' => $th]);

            return response()->json(['message' => 'Unable to confirm partner.'], 500);
        }
    }

    /**
     * POST /api/orders/submit-review
     *
     * Body: partner_id, order_id, rating, comment (optional)
     * Response: { success: true }
     *
     * @return JsonResponse
     */
    public function submitReview(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:partner_bills,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // check if already exist
        if (Review::where('partner_bill_id', $data['order_id'])->exists()) {
            return response()->json(['success' => true, 'message' => 'Bạn đã đánh giá đơn này rồi.']);
        }

        $partner = Partner::findOrFail($data['partner_id']);
        $order = PartnerBill::query()
            ->select(['id', 'code'])
            ->findOrFail($data['order_id']);

        $review = $partner->addReview([
            'review' => $data['comment'] ?? null,
            'ratings' => ['rating' => $data['rating']],
            'recommend' => true,
            'approved' => true,
        ], $request->user()->id);
        $review->partner_bill_id = $data['order_id'];
        $review->save();

        $notificationTitle = __('notification.new_review_received.title');
        $notificationBody = __('notification.new_review_received.body', ['code' => $order->code]);

        $this->fcmService->sendToUser(
            $partner,
            $notificationTitle,
            $notificationBody,
            ['code' => 'NEW_REVIEW_RECEIVED']
        );

        Notification::make()
            ->title($notificationTitle)
            ->body($notificationBody)
            ->info()
            ->sendToDatabase($partner, true);

        Statistical::syncPartnerRatingMetrics($partner->id);
        PartnerWidgetCacheService::clearPartnerCaches($partner->id);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/orders/validate-voucher
     *
     * Body: voucher_input, order_id
     * Response: { status, message, details }
     *
     * @return JsonResponse
     */
    public function validateVoucher(Request $request)
    {
        $data = $request->validate([
            'voucher_input' => 'required|string|min:5|max:20',
            'order_id' => 'required|integer|exists:partner_bills,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        $voucher = Voucher::where('code', $data['voucher_input'])->first();

        if (! $voucher || ! $partnerBill) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $now = now();
        $isExpired = $voucher->expires_at && $now->greaterThan($voucher->expires_at);
        $notStarted = $voucher->startAt() && $now->lessThan($voucher->startAt());
        $limitReached = ! $voucher->isUnlimited()
            && $voucher->usageLimit() !== null
            && $voucher->timesUsed() >= $voucher->usageLimit();

        $status = ! ($isExpired || $notStarted || $limitReached);

        $message = 'Voucher is valid.';
        if ($isExpired) {
            $message = 'Voucher expired.';
        } elseif ($notStarted) {
            $message = 'Voucher not yet valid.';
        } elseif ($limitReached) {
            $message = 'Voucher usage limit reached.';
        }

        if ($status) {
            $partnerBill->voucher_id = $voucher->id;
            $partnerBill->save();
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'details' => [
                'code' => $voucher->code,
                'discount_percent' => $voucher->discountPercentage(),
                'max_discount_amount' => $voucher->maxDiscountAmount(),
                'min_order_amount' => $voucher->minOrderAmount(),
                'usage_limit' => $voucher->usageLimit(),
                'times_used' => $voucher->timesUsed(),
                'is_unlimited' => $voucher->isUnlimited(),
                'starts_at' => optional($voucher->startAt())->toIso8601String(),
                'expires_at' => optional($voucher->expires_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/orders/voucher-discount
     *
     * Body: voucher_input, order_id, partner_id
     * Response: { status, message, discount }
     *
     * @return JsonResponse
     */
    public function getVoucherDiscountAmount(Request $request)
    {
        $data = $request->validate([
            'voucher_input' => 'required|string|min:5|max:20',
            'order_id' => 'required|integer|exists:partner_bills,id',
            'partner_id' => 'required|integer|exists:users,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        if (! $partnerBill) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
                'discount' => 0,
            ], 404);
        }

        $partner = User::find($data['partner_id']);
        if (! $partner) {
            return response()->json([
                'status' => false,
                'message' => 'Partner not found.',
                'discount' => 0,
            ], 404);
        }

        $partnerBillDetail = PartnerBillDetail::where('partner_bill_id', $partnerBill->id)
            ->where('partner_id', $partner->id)
            ->first();
        if (! $partnerBillDetail) {
            return response()->json([
                'status' => false,
                'message' => 'Partner detail not found.',
                'discount' => 0,
            ], 404);
        }

        $voucher = Voucher::where('code', $data['voucher_input'])->first();
        if (! $voucher) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found.',
                'discount' => 0,
            ], 404);
        }

        $result = $voucher->validate($partnerBillDetail->total);
        if (! $result->status) {
            return response()->json([
                'status' => false,
                'message' => $result->message,
                'discount' => 0,
            ], 422);
        }

        $discount = $voucher->getDiscountAmount($partnerBillDetail->total);

        return response()->json([
            'status' => true,
            'message' => 'Voucher is valid.',
            'discount' => $discount,
        ]);
    }

    /**
     * Remove the voucher from the order
     */
    public function removeVoucher(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer|exists:partner_bills,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        if (! $partnerBill) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $partnerBill->voucher_id = null;
        $partnerBill->save();

        return response()->json([
            'status' => true,
            'message' => 'Voucher removed successfully.',
        ]);
    }

    public function priceIncreaseRequests(Request $request, PartnerBill $order): JsonResponse
    {
        if ((int) $order->client_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $priceIncreaseRequests = $order->priceIncreaseRequests()
            ->latest()
            ->paginate($this->resolvePerPage($request, self::DEFAULT_PER_PAGE));

        return response()->json([
            'price_increase_requests' => $this->paginatedData(
                $priceIncreaseRequests,
                PartnerBillPriceIncreaseRequestResource::class,
            ),
        ]);
    }

    public function acceptPriceIncreaseRequest(
        AcceptPriceIncreaseRequest $request,
        PartnerBill $order,
        PartnerBillPriceIncreaseRequest $priceIncreaseRequest,
    ): JsonResponse {
        return DB::transaction(function () use ($request, $order, $priceIncreaseRequest) {
            $lockedOrder = PartnerBill::query()->lockForUpdate()->findOrFail($order->id);

            if ((int) $lockedOrder->client_id !== (int) $request->user()->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            $lockedPriceIncreaseRequest = PartnerBillPriceIncreaseRequest::query()
                ->lockForUpdate()
                ->findOrFail($priceIncreaseRequest->id);

            if ((int) $lockedPriceIncreaseRequest->partner_bill_id !== (int) $lockedOrder->id) {
                return response()->json(['message' => 'Price increase request does not belong to this order.'], 404);
            }

            if ($lockedPriceIncreaseRequest->status !== PartnerBillPriceIncreaseRequestStatus::Pending) {
                return response()->json(['message' => 'Price increase request has already been processed.'], 422);
            }

            if (! in_array($lockedOrder->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
                return response()->json(['message' => 'Order does not allow price changes.'], 422);
            }

            $newTotal = $lockedPriceIncreaseRequest->requested_total;
            $discount = $lockedOrder->voucher?->getDiscountAmount($newTotal) ?? 0;

            $lockedOrder->forceFill([
                'total' => $newTotal,
                'final_total' => max($newTotal - $discount, 0),
            ])->save();

            $lockedPriceIncreaseRequest->forceFill([
                'status' => PartnerBillPriceIncreaseRequestStatus::Accepted,
                'responded_by' => $request->user()->id,
                'responded_at' => now(),
            ])->save();

            $lockedOrder->priceIncreaseRequests()
                ->whereKeyNot($lockedPriceIncreaseRequest->id)
                ->where('status', PartnerBillPriceIncreaseRequestStatus::Pending->value)
                ->update([
                    'status' => PartnerBillPriceIncreaseRequestStatus::Superseded->value,
                    'responded_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $lockedOrder->id,
                    'total' => $lockedOrder->total,
                    'final_total' => $lockedOrder->final_total,
                ],
                'price_increase_request' => new PartnerBillPriceIncreaseRequestResource($lockedPriceIncreaseRequest),
            ]);
        });
    }

    public function rejectPriceIncreaseRequest(
        RejectPriceIncreaseRequest $request,
        PartnerBill $order,
        PartnerBillPriceIncreaseRequest $priceIncreaseRequest,
    ): JsonResponse {
        return DB::transaction(function () use ($request, $order, $priceIncreaseRequest) {
            $lockedOrder = PartnerBill::query()->lockForUpdate()->findOrFail($order->id);

            if ((int) $lockedOrder->client_id !== (int) $request->user()->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            $lockedPriceIncreaseRequest = PartnerBillPriceIncreaseRequest::query()
                ->lockForUpdate()
                ->findOrFail($priceIncreaseRequest->id);

            if ((int) $lockedPriceIncreaseRequest->partner_bill_id !== (int) $lockedOrder->id) {
                return response()->json(['message' => 'Price increase request does not belong to this order.'], 404);
            }

            if ($lockedPriceIncreaseRequest->status !== PartnerBillPriceIncreaseRequestStatus::Pending) {
                return response()->json(['message' => 'Price increase request has already been processed.'], 422);
            }

            $lockedPriceIncreaseRequest->forceFill([
                'status' => PartnerBillPriceIncreaseRequestStatus::Rejected,
                'responded_by' => $request->user()->id,
                'responded_at' => now(),
            ])->save();

            return response()->json([
                'success' => true,
                'price_increase_request' => new PartnerBillPriceIncreaseRequestResource($lockedPriceIncreaseRequest),
            ]);
        });
    }

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }
}
