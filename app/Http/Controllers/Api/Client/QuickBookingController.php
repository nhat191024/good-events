<?php

namespace App\Http\Controllers\Api\Client;

use App\Enum\PartnerBillStatus;

use App\Models\Event;
use App\Models\Location;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;

use App\Events\NewPartnerBillCreated;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BookingRequest;
use App\Http\Resources\Api\EventResource;
use App\Http\Resources\Api\PartnerBillResource;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuickBookingController extends Controller
{

    /**
     * GET /api/quick-booking/event-list
     *
     * Response: { event_list }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventList()
    {

        $events = Event::all();

        return response()->json(EventResource::collection($events));
    }

    /**
     * POST /api/quick-booking/submit
     *
     * Body: order_date, start_time, end_time, province_id, ward_id, event_id,
     * custom_event, location_detail, note, category_id
     * Response: { success: true, bill }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveBookingInfo(BookingRequest $request)
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
            'booking_photo' => 'nullable|image|max:20480|mimes:jpeg,png,jpg,webp',
            'booking_photos' => 'nullable|array|max:5',
            'booking_photos.*' => 'image|max:20480|mimes:jpeg,png,jpg,webp',
        ]);

        $allCategories = PartnerCategory::getAllCached();
        if ($allCategories->where('id', '=', $validated['category_id'])->where('parent_id', '=', null)->isNotEmpty()) {
            return response()->json([
                'message' => 'Please select a specific sub-category, not a top-level category.',
            ], 422);
        }

        $provinceItem = Location::find($validated['province_id']);
        $wardItem = $provinceItem?->wards()->find($validated['ward_id']);
        if (!$wardItem) {
            return response()->json([
                'message' => 'Invalid ward selection.',
            ], 422);
        }

        // return $request->user();

        $user = $request->user();

        $address = $validated['location_detail'] . ', ' . $wardItem->name . ', ' . $provinceItem->name;

        $newBill = PartnerBill::create([
            'code' => 'PB' . rand(10000, 999999),
            'address' => $address,
            'location_id' => $wardItem->id,
            'phone' => $user->phone,
            'date' => $validated['order_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'event_id' => $validated['event_id'] ?? null,
            'custom_event' => $validated['custom_event'] ?? null,
            'client_id' => $user->id,
            'category_id' => $validated['category_id'],
            'note' => $validated['note'] ?? null,
            'status' => PartnerBillStatus::PENDING,
        ]);

        $this->attachBookingPhoto($request, $newBill);

        NewPartnerBillCreated::dispatch($newBill);

        return response()->json([
            'success' => true,
            'bill' => PartnerBillResource::make($newBill)->resolve(),
        ]);
    }

    /**
     * GET /api/quick-booking/finish/{billCode}
     *
     * Response: { partner_bill, category_name }
     *
     * @param string $billCode
     * @return \Illuminate\Http\JsonResponse
     */
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

    private function attachBookingPhoto(Request $request, PartnerBill $bill): void
    {
        foreach ($this->bookingPhotoFiles($request) as $index => $file) {
            $this->attachBookingPhotoFile($file, $bill, $index + 1);
        }
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function bookingPhotoFiles(Request $request): array
    {
        $files = [];
        $bookingPhotos = $request->file('booking_photos');

        if (is_array($bookingPhotos)) {
            $files = array_merge($files, $bookingPhotos);
        } elseif ($bookingPhotos instanceof UploadedFile) {
            $files[] = $bookingPhotos;
        }

        if ($request->hasFile('booking_photo')) {
            $legacyPhoto = $request->file('booking_photo');

            if ($legacyPhoto instanceof UploadedFile) {
                $files[] = $legacyPhoto;
            }
        }

        return array_slice(array_values(array_filter(
            $files,
            fn ($file): bool => $file instanceof UploadedFile
        )), 0, 5);
    }

    private function attachBookingPhotoFile(UploadedFile $file, PartnerBill $bill, int $index): void
    {
        $fileName = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $temporaryPath = $file->storeAs('tmp/booking-photos', $fileName, 'local');

        if (! $temporaryPath) {
            return;
        }

        try {
            $bill->addMediaFromDisk($temporaryPath, 'local')
                ->usingName('Booking Photo ' . $index . ' - ' . $bill->code)
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('booking_photo');
        } finally {
            Storage::disk('local')->delete($temporaryPath);
        }
    }
}
