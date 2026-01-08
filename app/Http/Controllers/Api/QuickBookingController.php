<?php

namespace App\Http\Controllers\Api;

use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\PartnerBillResource;
use App\Models\Event;
use App\Models\Location;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;

class QuickBookingController extends Controller
{
    public function chooseCategory(): \Illuminate\Http\JsonResponse
    {
        $partnerCategories = PartnerCategory::getTree()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                    'min_price' => $category->min_price,
                    'max_price' => $category->max_price,
                    'description' => $category->description,
                    'media' => $this->getImageUrl($category),
                ];
            });

        return response()->json([
            'partner_categories' => $partnerCategories->values(),
        ]);
    }

    public function choosePartnerCategory(string $partnerCategorySlug)
    {
        $allCategories = PartnerCategory::getTree();
        $partnerCategory = $allCategories->where('slug', $partnerCategorySlug)->first();

        if (!$partnerCategory) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $partnerCategory->load([
            'children' => function ($query) {
                $query->orderBy('order', 'asc')
                    ->limit(8)
                    ->with('media');
            },
        ]);

        if ($partnerCategory->children->count() === 0) {
            return response()->json([
                'message' => 'Category has no children.',
            ], 422);
        }

        $parent = [
            'id' => $partnerCategory->id,
            'name' => $partnerCategory->name,
            'slug' => $partnerCategory->slug,
            'parent_id' => $partnerCategory->parent_id,
            'min_price' => $partnerCategory->min_price,
            'max_price' => $partnerCategory->max_price,
            'description' => $partnerCategory->description,
            'media' => $this->getImageUrl($partnerCategory),
        ];

        $children = $partnerCategory->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'parent_id' => $child->parent_id,
                'min_price' => $child->min_price,
                'max_price' => $child->max_price,
                'description' => $child->description,
                'media' => $this->getImageUrl($child),
            ];
        });

        return response()->json([
            'partner_category' => $parent,
            'partner_children_list' => $children->values(),
        ]);
    }

    public function fillOrderInfo(string $partnerCategorySlug, string $partnerChildCategorySlug)
    {
        $allCategories = PartnerCategory::getTree();
        $partnerCategory = $allCategories->where('slug', $partnerCategorySlug)->first();

        if (!$partnerCategory) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $partnerCategory->load(['children.media']);

        if ($partnerCategory->children->count() === 0) {
            return response()->json([
                'message' => 'Category has no children.',
            ], 422);
        }

        $searchItem = $partnerCategory->children->firstWhere('slug', $partnerChildCategorySlug);

        if (!$searchItem) {
            return response()->json([
                'message' => 'Invalid category selection.',
            ], 422);
        }

        $events = Event::all();
        $provinces = Location::whereNull('parent_id')
            ->select(['id', 'name', 'code', 'codename', 'short_codename', 'type', 'phone_code', 'parent_id'])
            ->orderBy('name')
            ->get();

        $parent = [
            'id' => $partnerCategory->id,
            'name' => $partnerCategory->name,
            'slug' => $partnerCategory->slug,
            'parent_id' => $partnerCategory->parent_id,
            'min_price' => $partnerCategory->min_price,
            'max_price' => $partnerCategory->max_price,
            'description' => $partnerCategory->description,
            'media' => $this->getImageUrl($partnerCategory),
        ];

        $child = [
            'id' => $searchItem->id,
            'name' => $searchItem->name,
            'slug' => $searchItem->slug,
            'parent_id' => $searchItem->parent_id,
            'min_price' => $searchItem->min_price,
            'max_price' => $searchItem->max_price,
            'description' => $searchItem->description,
            'media' => $this->getImageUrl($searchItem),
        ];

        return response()->json([
            'partner_category' => $parent,
            'partner_children_category' => $child,
            'event_list' => EventResource::collection($events),
            'provinces' => LocationResource::collection($provinces),
        ]);
    }

    public function saveBookingInfo(Request $request)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'province_id' => 'required|integer|exists:locations,id',
            'ward_id' => 'required|integer|exists:locations,id',
            'event_id' => 'nullable|integer|exists:events,id',
            'custom_event' => 'nullable|string|max:255',
            'location_detail' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
            'category_id' => 'required|integer|exists:partner_categories,id',
        ]);

        $provinceItem = Location::find($validated['province_id']);
        $wardItem = $provinceItem?->wards()->find($validated['ward_id']);
        if (!$wardItem) {
            return response()->json([
                'message' => 'Invalid ward selection.',
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $address = $validated['location_detail'] . ', ' . $wardItem->name . ', ' . $provinceItem->name;

        $newBill = PartnerBill::create([
            'code' => 'PB' . rand(10000, 999999),
            'address' => $address,
            'phone' => $user->phone,
            'date' => $validated['order_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'event_id' => $validated['event_id'],
            'custom_event' => $validated['custom_event'],
            'client_id' => $user->id,
            'category_id' => $validated['category_id'],
            'note' => $validated['note'],
            'status' => PartnerBillStatus::PENDING,
        ]);

        return response()->json([
            'success' => true,
            'bill' => PartnerBillResource::make($newBill)->resolve(),
        ]);
    }

    public function finishedBooking(string $billCode)
    {
        $bill = PartnerBill::where('code', $billCode)->with('category')->first();
        if (!$bill) {
            return response()->json([
                'message' => 'Bill not found.',
            ], 404);
        }

        return response()->json([
            'partner_bill' => PartnerBillResource::make($bill)->resolve(),
            'category_name' => $bill->category?->name,
        ]);
    }

    private function getImageUrl($model): ?string
    {
        if (!method_exists($model, 'getFirstMediaUrl')) {
            return null;
        }

        return $model->getFirstMediaUrl('images', 'thumb');
    }
}
