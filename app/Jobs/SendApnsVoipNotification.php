<?php

namespace App\Jobs;

use App\Models\PushDevice;
use App\Services\ApnsVoipService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendApnsVoipNotification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /** @param array<string, mixed> $payload */
    public function __construct(
        private readonly int $pushDeviceId,
        private readonly string $callId,
        private readonly array $payload,
    ) {
        $this->onQueue('notifications');
    }

    public function uniqueId(): string
    {
        return "apns-voip:{$this->pushDeviceId}:{$this->callId}";
    }

    public function uniqueFor(): int
    {
        return 60;
    }

    public function handle(ApnsVoipService $apnsVoipService): void
    {
        $device = PushDevice::query()->find($this->pushDeviceId);

        if ($device?->voip_token === null) {
            return;
        }

        if (! $apnsVoipService->isConfigured()) {
            Log::warning('Skipping APNs VoIP push because credentials are not configured.', [
                'push_device_id' => $device->id,
                'call_id' => $this->callId,
            ]);

            return;
        }

        $response = $apnsVoipService->send($device->voip_token, $this->payload);
        $reason = $response->json('reason');

        if ($response->status() === 410 || in_array($reason, ['BadDeviceToken', 'DeviceTokenNotForTopic', 'Unregistered'], true)) {
            $device->update(['voip_token' => null]);

            return;
        }

        $response->throw();
    }
}
