<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\PartnerServiceStatus;
use App\Models\PartnerService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\CreatePartnerServiceRequest;
use App\Http\Requests\Partner\UploadServiceImagesRequest;
use App\Http\Resources\Api\Partner\PartnerServiceCollection;
use App\Http\Resources\Api\Partner\PartnerServiceResource;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PartnerServiceController extends Controller
{
    /**
     * GET /partner/service/index
     *
     * @param Request $request
     * @return \App\Http\Resources\Api\Partner\PartnerServiceCollection
     */
    public function index()
    {
        $partnerServices = auth()->user()->partnerServices()
            ->with(['category'])
            ->withExists('serviceMedia')
            ->get();

        return new PartnerServiceCollection($partnerServices);
    }

    /**
     * POST /partner/service
     *
     * @param CreatePartnerServiceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CreatePartnerServiceRequest $request): \Illuminate\Http\JsonResponse
    {
        $partnerService = PartnerService::create([
            'category_id' => $request->validated('category_id'),
            'user_id' => auth()->id(),
            'status' => PartnerServiceStatus::PENDING,
        ]);

        if ($request->filled('service_media')) {
            $partnerService->serviceMedia()->createMany($request->validated('service_media'));
        }

        $partnerService = PartnerServiceResource::make($partnerService->load('category'));

        return response()->json([
            'success' => true,
            'data' => $partnerService,
        ], 201);
    }

    /**
     * GET /partner/service/{serviceId}
     *
     * @param int $serviceId
     * @return \App\Http\Resources\Api\Partner\PartnerServiceCollection
     */
    public function show($serviceId)
    {
        $partnerServices = PartnerService::query()
            ->where('id', $serviceId)
            ->with(['category', 'serviceMedia'])
            ->withExists('serviceMedia')
            ->get();

        return new PartnerServiceCollection($partnerServices);
    }

    /**
     * POST /api/partner/service/{serviceId}
     *
     * @param Request $request
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $serviceId)
    {
        if (!auth()->user()->partnerServices()->where('id', $serviceId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $categoryId = $request->input('category_id');

        $partnerService = PartnerService::findOrFail($serviceId);

        $partnerService->category_id = $categoryId;
        $partnerService->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * POST /partner/service/{serviceId}/images
     *
     * @param UploadServiceImagesRequest $request
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImages(UploadServiceImagesRequest $request, int $serviceId): \Illuminate\Http\JsonResponse
    {
        $partnerService = PartnerService::findOrFail($serviceId);

        if ($partnerService->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $existingCount = $partnerService->getMedia('service_images')->count();
        $newCount = count($request->file('images'));

        if ($existingCount + $newCount > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Tổng số ảnh không được vượt quá 10. Hiện có ' . $existingCount . ' ảnh.',
            ], 422);
        }

        $uploaded = [];

        foreach ($request->file('images') as $image) {
            $media = $partnerService
                ->addMedia($image)
                ->toMediaCollection('service_images');

            $uploaded[] = [
                'id' => $media->id,
                'url' => $media->getTemporaryUrl(now()->addMinutes(5)),
                'thumb' => $media->getTemporaryUrl(now()->addMinutes(5), 'thumb'),
                'file_name' => $media->file_name,
                'size' => $media->size,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $uploaded,
        ], 201);
    }

    /**
     * DELETE /partner/service/{serviceId}/images/{mediaId}
     *
     * @param int $serviceId
     * @param int $mediaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $serviceId, int $mediaId): \Illuminate\Http\JsonResponse
    {
        $partnerService = PartnerService::findOrFail($serviceId);

        if ($partnerService->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        /** @var Media $media */
        $media = Media::where('id', $mediaId)
            ->where('model_type', PartnerService::class)
            ->where('model_id', $serviceId)
            ->where('collection_name', 'service_images')
            ->firstOrFail();

        $media->delete();

        return response()->json(['success' => true]);
    }

    /**
     * GET /partner/service/{serviceId}/images
     *
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(int $serviceId): \Illuminate\Http\JsonResponse
    {
        $partnerService = PartnerService::findOrFail($serviceId);

        if ($partnerService->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $images = $partnerService->getMedia('service_images')->map(fn(Media $media) => [
            'id' => $media->id,
            'url' => $media->getTemporaryUrl(now()->addMinutes(5)),
            'thumb' => $media->getTemporaryUrl(now()->addMinutes(5), 'thumb'),
            'file_name' => $media->file_name,
            'size' => $media->size,
            'created_at' => $media->created_at?->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }
}
