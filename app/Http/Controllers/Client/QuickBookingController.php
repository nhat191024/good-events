<?php

namespace App\Http\Controllers\Client;

use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\QuickBookingService;
use App\Http\Requests\Client\BookingRequest;
use App\Models\Event;
use App\Models\Location;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 *  @property PartnerBillStatus $status
 */
class QuickBookingController extends Controller
{
    private $quickBookingService = null;
    //? error messages
    public const CATEGORY_NOT_FOUND = 'Không tìm thấy danh mục bạn vừa chọn.';
    public const CATEGORY_CHILD_INVALID = 'Cây danh mục không khớp, vui lòng chọn lại.';
    public const PARENT_HAS_NO_CHILD = 'Danh mục đang bảo trì, hãy thử danh mục khác hoặc liên hệ quản trị viên!';
    public function __construct() {
        $this->quickBookingService = app(QuickBookingService::class);
    }

    /**
     * Index page (step 1)
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function chooseCategory(Request $request)
    {
        $partnerCategories = PartnerCategory::where('parent_id', '=', null)
            ->with("media")
            ->get();
        return Inertia::render("booking/QuickBooking", [
            "partnerCategories" => $partnerCategories
        ]);
    }

    /**
     * Choose partner category (2nd step)
     * @param string $partner_category_slug
     * @return \Inertia\Response | \Illuminate\Http\RedirectResponse;
     */
    public function choosePartnerCategory(string $partner_category_slug)
    {
        $partnerCategory = PartnerCategory::where("slug", $partner_category_slug)->with("media")->first();

        if (!$partnerCategory) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_NOT_FOUND);
        }

        $partnerChildrenList = $partnerCategory->children()->with("media")->get();

        if ($partnerChildrenList->count() == 0) {
            return $this->quickBookingService->goBackWithError(self::PARENT_HAS_NO_CHILD);
        }
        // dd($category_slug, !$partnerCategory, $partnerCategory, $partnerChildrenList);

        return Inertia::render("booking/QuickBookingSecond", [
            "partnerChildrenList" => $partnerChildrenList,
            "partnerCategory" => $partnerCategory,
        ]);
    }

    /**
     * Final step, fillin in the order info
     * @param string $partner_category_slug
     * @param string $partner_child_category_slug
     * @return \Inertia\Response | \Illuminate\Http\RedirectResponse;
     */
    public function fillOrderInfo(string $partner_category_slug, string $partner_child_category_slug)
    {
        $partnerCategory = PartnerCategory::where("slug", $partner_category_slug)->first();

        if (!$partnerCategory) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_NOT_FOUND);
        }

        $partnerChildrenListQuery = $partnerCategory->children();

        if ($partnerChildrenListQuery->get()->count() == 0) {
            return $this->quickBookingService->goBackWithError(self::PARENT_HAS_NO_CHILD);
        }

        // ensure the category is in fact that parent's child - and not other parent's child
        $searchItem = $partnerChildrenListQuery->where('slug', $partner_child_category_slug)->with("media")->first();
        if (! $searchItem) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_CHILD_INVALID);
        }

        $events = Event::all();
        $provinces = Location::whereNull('parent_id')->select(['id','name'])->orderBy('name')->get();

        return Inertia::render('booking/QuickBookingDetail', [
            'partnerCategory' => $partnerCategory,
            'partnerChildrenCategory' => $searchItem,
            'eventList' => $events,
            'provinces' => $provinces,
        ]);
    }

    /**
     * save order info (final)
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response | \Illuminate\Http\RedirectResponse;
     */
    public function saveBookingInfo(BookingRequest $request)
    {
        $orderDate = $request->input("order_date");
        $startTime = $request->input("start_time");
        $endTime = $request->input("end_time");
        $provinceId = $request->input("province_id");
        $wardId = $request->input("ward_id");
        $eventId = $request->input("event_id");
        $locationDetail = $request->input("location_detail");
        $note = $request->input("note");
        $categoryId = $request->input("category_id");

        $provinceItem = Location::find( $provinceId );

        // ensure the category IS not parent
        if (PartnerCategory::where("id","=",$categoryId)->where("parent_id","=",null)->exists()) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_CHILD_INVALID);
        }

        // ensure correct location parent-children tree
        $wardItem = $provinceItem->wards()->find( $wardId );
        if (!$wardItem) {
            return back()->withErrors(['ward_id' => 'Vui lòng chọn đúng phường/xã của tỉnh '. $provinceItem->name .'.']);
        }

        $user = Auth::user();
        $address = $locationDetail . ', ' . $wardItem->name . ', ' . $provinceItem->name;
        // $phone = $user->phone;
        // $clientId = $user->id;
        $phone = '098776543';
        $clientId = 1;

        $newBill = PartnerBill::create([
            'code' => 'PB' . rand(10000, 999999),
            'address' => $address,
            'phone' => $phone,
            'date' => $orderDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_id' => $eventId,
            'client_id' => $clientId,
            'category_id' => $categoryId,
            'note' => $note,
            'status' => PartnerBillStatus::PENDING,
        ]);

        return redirect()->route('quick-booking.finish',['bill_id'=> $newBill->code]);
    }

    public function finishedBooking(string $billCode) {;
        $newBill = PartnerBill::where('code', $billCode)->first();
        // dd($newBill);
        if (!$newBill) {
            return redirect()->route('home');
        }
        return Inertia::render("booking/Finished", [
            'partnerBill' => $newBill
        ]);
    }
}
