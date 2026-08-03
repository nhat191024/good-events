<?php

namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertPushDeviceRequest;
use App\Models\PushDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushDeviceController extends Controller
{
    public function store(UpsertPushDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $attributes = [
            'platform' => $validated['platform'],
            'last_seen_at' => now(),
        ];

        foreach (['fcm_token', 'voip_token'] as $tokenType) {
            if (array_key_exists($tokenType, $validated)) {
                $attributes[$tokenType] = $validated[$tokenType];
            }
        }

        $device = DB::transaction(function () use ($request, $validated, $attributes): PushDevice {
            foreach (['fcm_token', 'voip_token'] as $tokenType) {
                if (! empty($validated[$tokenType])) {
                    PushDevice::query()
                        ->where($tokenType, $validated[$tokenType])
                        ->update([$tokenType => null]);
                }
            }

            return $request->user()->pushDevices()->updateOrCreate(
                ['device_id' => $validated['device_id']],
                $attributes,
            );
        });

        return response()->json([
            'device' => [
                'device_id' => $device->device_id,
                'platform' => $device->platform,
                'has_fcm_token' => $device->fcm_token !== null,
                'has_voip_token' => $device->voip_token !== null,
                'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Request $request, string $deviceId): JsonResponse
    {
        $request->user()
            ->pushDevices()
            ->where('device_id', $deviceId)
            ->delete();

        return response()->json(['success' => true]);
    }
}
