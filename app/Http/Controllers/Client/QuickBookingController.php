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
    public function __construct()
    {
        $this->quickBookingService = app(QuickBookingService::class);
    }

    /**
     * Index page (step 1)
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function chooseCategory(Request $request)
    {
        $expireAt = now()->addMinutes(5);

        $partnerCategories = PartnerCategory::where('parent_id', '=', null)
            ->with("media")
            ->get()
            ->map(function ($category) use ($expireAt) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                    'min_price' => $category->min_price,
                    'max_price' => $category->max_price,
                    'description' => $category->description,
                    'deleted_at' => $category->deleted_at,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                    'media' => $this->getTemporaryImageUrl($category, $expireAt)
                ];
            });

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
        $expireAt = now()->addMinutes(5);

        $partnerCategory = PartnerCategory::where("slug", $partner_category_slug)
            ->with([
                'media',
                'children.media'
            ])
            ->first();

        if (!$partnerCategory) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_NOT_FOUND);
        }

        if ($partnerCategory->children->count() == 0) {
            return $this->quickBookingService->goBackWithError(self::PARENT_HAS_NO_CHILD);
        }

        $transformedParentCategory = [
            'id' => $partnerCategory->id,
            'name' => $partnerCategory->name,
            'slug' => $partnerCategory->slug,
            'parent_id' => $partnerCategory->parent_id,
            'min_price' => $partnerCategory->min_price,
            'max_price' => $partnerCategory->max_price,
            'description' => $partnerCategory->description,
            'deleted_at' => $partnerCategory->deleted_at,
            'created_at' => $partnerCategory->created_at,
            'updated_at' => $partnerCategory->updated_at,
            'media' => $this->getTemporaryImageUrl($partnerCategory, $expireAt)
        ];

        $transformedChildrenList = $partnerCategory->children->map(function ($child) use ($expireAt) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'parent_id' => $child->parent_id,
                'min_price' => $child->min_price,
                'max_price' => $child->max_price,
                'description' => $child->description,
                'deleted_at' => $child->deleted_at,
                'created_at' => $child->created_at,
                'updated_at' => $child->updated_at,
                'media' => $this->getTemporaryImageUrl($child, $expireAt)
            ];
        });

        return Inertia::render("booking/QuickBookingSecond", [
            "partnerChildrenList" => $transformedChildrenList,
            "partnerCategory" => $transformedParentCategory,
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
        $expireAt = now()->addMinutes(5);

        $partnerCategory = PartnerCategory::where("slug", $partner_category_slug)
            ->with(['media', 'children.media'])
            ->first();

        if (!$partnerCategory) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_NOT_FOUND);
        }

        if ($partnerCategory->children->count() == 0) {
            return $this->quickBookingService->goBackWithError(self::PARENT_HAS_NO_CHILD);
        }

        $searchItem = $partnerCategory->children->firstWhere('slug', $partner_child_category_slug);

        if (!$searchItem) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_CHILD_INVALID);
        }

        $events = Event::all()->map(function ($event) use ($expireAt) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'deleted_at' => $event->deleted_at,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ];
        });

        $provinces = Location::whereNull('parent_id')->select(['id', 'name'])->orderBy('name')->get();

        $transformedParentCategory = [
            'id' => $partnerCategory->id,
            'name' => $partnerCategory->name,
            'slug' => $partnerCategory->slug,
            'parent_id' => $partnerCategory->parent_id,
            'min_price' => $partnerCategory->min_price,
            'max_price' => $partnerCategory->max_price,
            'description' => $partnerCategory->description,
            'deleted_at' => $partnerCategory->deleted_at,
            'created_at' => $partnerCategory->created_at,
            'updated_at' => $partnerCategory->updated_at,
            'media' => $this->getTemporaryImageUrl($partnerCategory, $expireAt)
        ];

        $transformedChildCategory = [
            'id' => $searchItem->id,
            'name' => $searchItem->name,
            'slug' => $searchItem->slug,
            'parent_id' => $searchItem->parent_id,
            'min_price' => $searchItem->min_price,
            'max_price' => $searchItem->max_price,
            'description' => $searchItem->description,
            'deleted_at' => $searchItem->deleted_at,
            'created_at' => $searchItem->created_at,
            'updated_at' => $searchItem->updated_at,
            'media' => $this->getTemporaryImageUrl($searchItem, $expireAt)
        ];

        return Inertia::render('booking/QuickBookingDetail', [
            'partnerCategory' => $transformedParentCategory,
            'partnerChildrenCategory' => $transformedChildCategory,
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
        $eventCustom = $request->input("custom_event");
        $locationDetail = $request->input("location_detail");
        $note = $request->input("note");
        $categoryId = $request->input("category_id");

        $provinceItem = Location::find($provinceId);

        if (PartnerCategory::where("id", "=", $categoryId)->where("parent_id", "=", null)->exists()) {
            return $this->quickBookingService->goBackWithError(self::CATEGORY_CHILD_INVALID);
        }

        $wardItem = $provinceItem->wards()->find($wardId);
        if (!$wardItem) {
            return back()->withErrors(['ward_id' => 'Vui lòng chọn đúng phường/xã của tỉnh ' . $provinceItem->name . '.']);
        }

        $user = Auth::user();
        $address = $locationDetail . ', ' . $wardItem->name . ', ' . $provinceItem->name;
        $phone = $user->phone;
        $clientId = $user->id;
        // $phone = '0987765431';
        // $clientId = 1;

        $newBill = PartnerBill::create([
            'code' => 'PB' . rand(10000, 999999),
            'address' => $address,
            'phone' => $phone,
            'date' => $orderDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_id' => $eventId,
            'custom_event' => $eventCustom,
            'client_id' => $clientId,
            'category_id' => $categoryId,
            'note' => $note,
            'status' => PartnerBillStatus::PENDING,
        ]);

        $newBill->save();

        return redirect()->route('quick-booking.finish', ['bill_code' => $newBill->code]);
    }

    public function finishedBooking(string $billCode)
    {
        $newBill = PartnerBill::where('code', $billCode)->first();
        if (!$newBill) {
            return redirect()->route('home');
        }
        return Inertia::render("booking/Finished", [
            'partnerBill' => $newBill
        ]);
    }

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (!method_exists($model, 'getFirstTemporaryUrl')) {
            return null;
        }

        try {
            return $model->getFirstTemporaryUrl($expireAt, 'images');
        } catch (\Throwable $e) {
            return method_exists($model, 'getFirstMediaUrl')
                ? $model->getFirstMediaUrl('images')
                : null;
        }
    }
}
