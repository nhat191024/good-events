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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\OrderHistory\PartnerBillResource;
use App\Models\PartnerBillDetail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 *  @property PartnerBillStatus $status
 */
class OrderController extends Controller
{
    // private $quickBookingService = null;
    //? error messages
    public const RECORD_PER_PAGE = 20;
    // todo: only get this user's bills, temporary disabled for better testing
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? $request->ip();
        $lock = Cache::lock("orders:index:{$userId}", 2);
        if (! $lock->get()) {
            abort(429, 'Too many requests');
        }

        try {
            //code...
            return Inertia::render('orders/OrderManagementDashboard', [
                // 'orderList' => PartnerBillResource::collection($bills),
                'orderList' => Inertia::lazy(fn() => $this->getOrderList($request)),
                'orderListDetails' => Inertia::lazy(fn() => $this->getPartnerBillDetails($request)),
                'orderHistoryList' => Inertia::lazy(fn() => $this->getOrderHistoryList($request)),
                'singleOrder' => Inertia::lazy(fn() => $this->getSingleOrder($request)),
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('home');
        } finally {
            $lock->release();
        }
    }

    public function getSingleOrder(Request $request)
    {
        $orderId = (int) $request->query('single_order');
        if (!$orderId) return null;

        // Fetch the order regardless of status
        $order = PartnerBill::query()
            ->where('id', $orderId)
            // ->where('client_id', $request->user()->id)
            // ->with(['partner:id,name,avatar', 'partner.partnerProfile'])
            ->with('category', 'category.parent', 'event', 'details')
            ->first();

        return $order ? new PartnerBillResource($order) : null;
    }

    public function getPartnerBillDetails(Request $request)
    {
        $billId = (int) $request->query('active');
        if (!$billId) return null;

        // chọn cột gọn, eager partner cho resource
        $details = PartnerBillDetail::query()
            // ->where('user_id', $request->user()->id)
            ->where('partner_bill_id', $billId)
            ->with(['partner:id,name,avatar', 'partner.statistics', 'partner.partnerProfile'])
            ->select(['id', 'partner_bill_id', 'partner_id', 'total', 'status', 'updated_at'])
            ->orderByDesc('id')
            ->get();

        $billUpdatedTs = optional(
            PartnerBill::select(['id', 'updated_at'])->find($billId)
        )->updated_at?->timestamp;

        return [
            'billId'  => $billId,
            'items'   => PartnerBillDetailResource::collection($details),
            'version' => $billUpdatedTs,
        ];
    }

    public function getOrderHistoryList(Request $request)
    {
        $page = max(1, (int) $request->query('history_page', 1));

        $bills = PartnerBill::query()
            // ->where('user_id', $request->user()->id)
            ->with('category', 'category.parent', 'event', 'partner', 'partner.statistics', 'partner.partnerProfile')
            ->where('status', '!=', PartnerBillStatus::PENDING)
            ->orderByDesc('id') // ổn định thứ tự, tránh drift
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'history_page', $page);

        return PartnerBillHistoryResource::collection($bills);
    }

    public function getOrderList(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));

        $bills = PartnerBill::query()
            ->with('category', 'category.parent', 'event', 'details')
            ->where('status', '=', PartnerBillStatus::PENDING) // dùng '=' cho an toàn sql
            ->orderByDesc('id')
            ->paginate(self::RECORD_PER_PAGE, ['*'], 'page', $page);

        return PartnerBillResource::collection($bills);
    }

    public function cancelOrder(CancelOrderRequest $request)
    {
        $bill_id = $request->input('order_id');

        $bill = PartnerBill::findOrFail($bill_id);
        // dd('cancel order', $bill);

        $bill->status = PartnerBillStatus::CANCELLED;
        $bill->save();
    }

    public function confirmChoosePartner(ConfirmPartnerRequest $request)
    {
        $bill_id = $request->input('order_id');
        $user_id = $request->input('partner_id');
        // todo: add check if any of the current bill detail of the same bill has closed state already?
        // todo: need to add bill staus 'confirmed' here
        $billDetail = PartnerBill::findOrFail($bill_id)->details()->where('partner_id', $user_id)->first();
        $bill = PartnerBill::findOrFail($bill_id);
        $bill->partner_id = $billDetail->partner_id;
        $billDetail->status = PartnerBillDetailStatus::CLOSED;
        $billDetail->save();
        $bill->save();
        // dd('confirm choose partner', $billDetail);
    }
}
