<?php

namespace App\Http\Controllers\Client;

use App\Enum\PartnerBillDetailStatus;
use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\QuickBookingService;
use App\Http\Requests\Client\BookingRequest;
use App\Http\Requests\Client\OrderHistory\CancelOrderRequest;
use App\Http\Requests\Client\OrderHistory\ConfirmPartnerRequest;
use App\Http\Resources\OrderHistory\PartnerBillDetailResource;
use App\Http\Resources\OrderHistory\PartnerBillHistoryResource;
use App\Models\Event;
use App\Models\Location;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderCompleted;
use App\Notifications\OrderCreated;
use App\Notifications\OrderStatusChanged;
use App\Notifications\PartnerAcceptedOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\OrderHistory\PartnerBillResource;
use App\Models\PartnerBillDetail;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public const RECORD_PER_PAGE = 20;

    public function index(Request $request)
    {
        try {
            return Inertia::render('orders/OrderManagementDashboard', [
                'orderList' => Inertia::lazy(fn() => $this->getOrderList($request)),
                'orderListDetails' => Inertia::lazy(fn() => $this->getPartnerBillDetails($request)),
                'orderHistoryList' => Inertia::lazy(fn() => $this->getOrderHistoryList($request)),
                'singleOrder' => Inertia::lazy(fn() => $this->getSingleOrder($request)),
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('home');
        }
    }

    public function getSingleOrder(Request $request)
    {
        $orderId = (int) $request->query('single_order');
        if (!$orderId)
            return null;

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

        return $order ? new PartnerBillResource($order) : null;
    }

    public function getPartnerBillDetails(Request $request)
    {
        $billId = (int) $request->query('active');
        if (!$billId)
            return null;

        $details = PartnerBillDetail::query()
            ->where('partner_bill_id', $billId)
            ->with([
                'partner:id,name,avatar',
                'partner.statistics',
                'partner.partnerProfile'
            ])
            ->select(['id', 'partner_bill_id', 'partner_id', 'total', 'status', 'updated_at'])
            ->orderByDesc('id')
            ->get();

        $billUpdatedTs = optional(
            PartnerBill::select(['id', 'updated_at'])->find($billId)
        )->updated_at?->timestamp;

        return [
            'billId' => $billId,
            'items' => PartnerBillDetailResource::collection($details),
            'version' => $billUpdatedTs,
        ];
    }

    public function getOrderHistoryList(Request $request)
    {
        $page = max(1, (int) $request->query('history_page', 1));

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
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'history_page', $page);

        return PartnerBillHistoryResource::collection($bills);
    }

    public function getOrderList(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));

        $bills = PartnerBill::query()
            ->with([
                'category.media',
                'category.parent.media',
                'event',
                'details',
                'partner.statistics',
                'partner.partnerProfile'
            ])->where('client_id', $request->user()->id)
            ->whereIn('status', [
                PartnerBillStatus::PENDING,
                PartnerBillStatus::CONFIRMED,
                PartnerBillStatus::IN_JOB,
            ])
            ->orderByDesc('id')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        return PartnerBillResource::collection($bills);
    }

    public function cancelOrder(CancelOrderRequest $request)
    {
        $bill_id = $request->input('order_id');
        $bill = PartnerBill::findOrFail($bill_id);

        $bill->status = PartnerBillStatus::CANCELLED;
        $bill->save();

        $client = $bill->client;
        OrderCancelled::send($bill, $client);

        if ($bill->partner) {
            OrderCancelled::send($bill, $bill->partner);
        }
    }

    public function confirmChoosePartner(ConfirmPartnerRequest $request)
    {
        Log::debug('[controller] confirming partner request...', $request->all());
        try {
            $bill_id = $request->input('order_id');
            $user_id = $request->input('partner_id');
            $voucher_code = $request->input('voucher_code');

            $bill = PartnerBill::findOrFail($bill_id);
            $partnerBillDetail = PartnerBillDetail::where('partner_bill_id', $bill_id)
                ->where('partner_id', $user_id)
                ->first();

            $discount = 0;

            $voucher = Voucher::where('code', $voucher_code)->first();
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

            $client = $bill->client;
            PartnerAcceptedOrder::send($bill, $client);

            $partner = $bill->partner;
            OrderStatusChanged::send($bill, $partner, PartnerBillStatus::CONFIRMED);
        } catch (\Throwable $th) {
            Log::error('error in confirming choose partner', context: ['exception' => $th]);
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
        $bill = PartnerBill::findOrFail($data['order_id']);

        $partner->addReview([
            'review' => $data['comment'],
            'ratings' => ['rating' => $data['rating']],
            'recommend' => true,
            'approved' => true,
            'partner_bill_id' => $data['order_id'],
        ], $request->user()->id);

        $latest = \Codebyray\ReviewRateable\Models\Review::where('reviewable_type', User::class)
            ->where('reviewable_id', $partner->id)
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        if ($latest) {
            $latest->partner_bill_id = $data['order_id'];
            $latest->save();
        }

        return back()->with('review_submitted', true);
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
            return (object) [
                'status' => false,
                'message' => __('Voucher không tồn tại')
            ];
        }
        $result = $voucher->validate($partnerBill->total);
        return $result;
    }

    /**
     * Get the discounted amount of a choosen partner of a bill
     * @param \Illuminate\Http\Request $request
     * @return object
     */
    public function getVoucherDiscountAmount(Request $request)
    {
        $data = $request->validate([
            'voucher_input' => 'required|string|min:5|max:20',
            'order_id' => 'required|integer|exists:partner_bills,id',
            'partner_id' => 'required|integer|exists:users,id',
        ]);

        $partnerBill = PartnerBill::find($data['order_id']);
        if (!$partnerBill)
            return self::voucherFail(__('Đơn hàng không tồn tại'));
        // or, user
        $partner = User::find($data['partner_id']);
        if (!$partner)
            return self::voucherFail(__('Đối tác không tồn tại'));

        $partnerBillDetail = PartnerBillDetail::where('partner_bill_id', $partnerBill->id)
            ->orWhere('partner_id', $partner->id)->first();
        if (!$partnerBillDetail)
            return self::voucherFail(__('Đối tác không tồn tại'));

        $voucher = Voucher::where('code', $data['voucher_input'])->first();
        if (!$voucher)
            return self::voucherFail(__('Voucher không tồn tại'));

        $result = $voucher->validate($partnerBillDetail->total);
        if (!$result->status)
            return self::voucherFail(__($result->message));

        $discount = $voucher->getDiscountAmount($partnerBillDetail->total);

        return (object) [
            'status' => true,
            'message' => 'Voucher này khả dụng!',
            'discount' => $discount,
        ];
    }

    private function voucherFail(string $message)
    {
        return (object) [
            'status' => false,
            'message' => $message,
            'discount' => 0,
        ];
    }
}
