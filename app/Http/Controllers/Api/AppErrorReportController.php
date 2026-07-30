<?php

namespace App\Http\Controllers\Api;

use App\Enum\AppErrorSeverity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppErrorReportRequest;
use App\Models\AppErrorReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AppErrorReportController extends Controller
{
    public function store(StoreAppErrorReportRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        $validated = $request->validated();

        $report = AppErrorReport::query()->create([
            ...$validated,
            'uuid' => (string) Str::uuid(),
            'user_id' => $user?->getAuthIdentifier(),
            'severity' => $validated['severity'] ?? AppErrorSeverity::Error->value,
            'api_method' => isset($validated['api_method'])
                ? strtoupper($validated['api_method'])
                : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'occurred_at' => $validated['occurred_at'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận báo cáo lỗi.',
            'report_id' => $report->uuid,
        ], 201);
    }
}
