<?php

namespace App\Http\Controllers\Api;

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Api\Concerns\PaginatesApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\OrderHistory\CancelOrderRequest;
use App\Http\Requests\Client\OrderHistory\ConfirmPartnerRequest;
use App\Http\Resources\Api\PartnerBillDetailResource;
use App\Http\Resources\Api\PartnerBillHistoryResource;
use App\Http\Resources\Api\PartnerBillResource;
use App\Http\Resources\Api\PartnerProfileResource;
use App\Http\Resources\Api\PartnerServiceResource;
use App\Http\Resources\Api\UserResource;
use App\Models\PartnerBill;
use App\Models\PartnerBillDetail;
use App\Models\Statistical;
use App\Models\User;
use App\Models\Voucher;
use Codebyray\ReviewRateable\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use PaginatesApi;

    private const DEFAULT_PER_PAGE = 20;
    private const MAX_PER_PAGE = 50;

    public function list(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $bills = PartnerBill::query()
            ->with([
                'category.media',
                'category.parent.media',
                'event',
                'details',
                'partner.statistics',
                'partner.partnerProfile',
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

    public function history(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = $this->resolvePerPage($request, self::DEFAULT_PER_PAGE);

        $bills = PartnerBill::query()
            ->where('client_id', $request->user()->id)
            ->with([
                'category.media',
                'category.parent.media',
                'event',
                'partner.statistics',
                'partner.partnerProfile',
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

    public function single(Request $request, int $orderId)
    {
        $order = PartnerBill::query()
            ->where('id', $orderId)
            ->where('client_id', $request->user()->id)
            ->with([
                'category.media',
                'category.parent.media',
                'event',
                'details',
                'partner.statistics',
                'partner.partnerProfile',
            ])
            ->first();

        return response()->json([
            'order' => $order ? new PartnerBillResource($order) : null,
        ]);
    }

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

    public function partnerProfile(User $user)
    {
        $user->loadMissing('partnerProfile', 'partnerServices.category', 'partnerServices.media');

        if (!$user->partnerProfile) {
            return response()->json(['message' => 'Partner profile not found.'], 404);
        }

        return response()->json([
            'user' => new UserResource($user),
            'partner_profile' => new PartnerProfileResource($user->partnerProfile),
            'services' => PartnerServiceResource::collection($user->partnerServices)->resolve(),
        ]);
    }

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
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $startDate . ' ' . $startTime, $tz);
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

            if (!$partnerBillDetail) {
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

    public function submitReview(Request $request)
    {
        $data = $request->validate([
            'partner_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:partner_bills,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $partner = User::findOrFail($data['partner_id']);

        $partner->addReview([
            'review' => $data['comment'],
            'ratings' => ['rating' => $data['rating']],
            'recommend' => true,
            'approved' => true,
            'partner_bill_id' => $data['order_id'],
        ], $request->user()->id);

        $latest = Review::where('reviewable_type', User::class)
            ->where('reviewable_id', $partner->id)
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        if ($latest) {
            $latest->partner_bill_id = $data['order_id'];
            $latest->save();
        }

        Statistical::syncPartnerRatingMetrics($partner->id);

        return response()->json(['success' => true]);
    }

    public function validateVoucher(Request $request)
    {
        $data = $request->validate([
            'voucher_input' => 'required|string|min:5|max:20',
            'order_id' => 'required|integer|exists:partner_bills,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        $voucher = Voucher::where('code', $data['voucher_input'])->first();

        if (!$voucher || !$partnerBill) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $now = now();
        $isExpired = $voucher->expires_at && $now->greaterThan($voucher->expires_at);
        $notStarted = $voucher->startAt() && $now->lessThan($voucher->startAt());
        $limitReached = !$voucher->isUnlimited()
            && $voucher->usageLimit() !== null
            && $voucher->timesUsed() >= $voucher->usageLimit();

        $status = !($isExpired || $notStarted || $limitReached);

        $message = 'Voucher is valid.';
        if ($isExpired) {
            $message = 'Voucher expired.';
        } elseif ($notStarted) {
            $message = 'Voucher not yet valid.';
        } elseif ($limitReached) {
            $message = 'Voucher usage limit reached.';
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

    public function getVoucherDiscountAmount(Request $request)
    {
        $data = $request->validate([
            'voucher_input' => 'required|string|min:5|max:20',
            'order_id' => 'required|integer|exists:partner_bills,id',
            'partner_id' => 'required|integer|exists:users,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        if (!$partnerBill) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
                'discount' => 0,
            ], 404);
        }

        $partner = User::find($data['partner_id']);
        if (!$partner) {
            return response()->json([
                'status' => false,
                'message' => 'Partner not found.',
                'discount' => 0,
            ], 404);
        }

        $partnerBillDetail = PartnerBillDetail::where('partner_bill_id', $partnerBill->id)
            ->where('partner_id', $partner->id)
            ->first();
        if (!$partnerBillDetail) {
            return response()->json([
                'status' => false,
                'message' => 'Partner detail not found.',
                'discount' => 0,
            ], 404);
        }

        $voucher = Voucher::where('code', $data['voucher_input'])->first();
        if (!$voucher) {
            return response()->json([
                'status' => false,
                'message' => 'Voucher not found.',
                'discount' => 0,
            ], 404);
        }

        $result = $voucher->validate($partnerBillDetail->total);
        if (!$result->status) {
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

    private function resolvePerPage(Request $request, int $default): int
    {
        $perPage = (int) $request->query('per_page', $default);
        $perPage = max(1, $perPage);

        return min(self::MAX_PER_PAGE, $perPage);
    }
}
