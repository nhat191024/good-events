<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'service' => 'good-events',
        ]);
    }

    /**
     * Simple ping endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'message' => 'pong',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
