<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayOSWebhookRequest;
use App\Services\PaymentService;
use App\Services\PayOSWebhookService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayOSWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        PayOSWebhookRequest $request,
        PaymentService $paymentService,
        PayOSWebhookService $webhookService,
    ): JsonResponse {
        try {
            $data = $paymentService->verifyWebhook($request->all());
            $result = $webhookService->handle(
                $request->boolean('success'),
                $request->string('code')->toString(),
                $data,
            );
        } catch (DomainException $exception) {
            Log::warning('PayOS webhook data does not match the local payment', [
                'message' => $exception->getMessage(),
                'order_code' => $request->input('data.orderCode'),
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::warning('PayOS webhook signature verification failed', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result,
        ]);
    }
}
