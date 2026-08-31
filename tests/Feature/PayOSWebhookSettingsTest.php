<?php

use App\Services\PaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('confirms the webhook URL with the PayOS API', function (): void {
    config()->set([
        'services.payos.base_url' => 'https://api-merchant.payos.vn',
        'services.payos.client_id' => 'client-id',
        'services.payos.api_key' => 'api-key',
        'services.payos.checksum_key' => 'checksum-key',
    ]);
    Http::fake([
        'https://api-merchant.payos.vn/confirm-webhook' => Http::response([
            'code' => '00',
            'desc' => 'success',
            'data' => [
                'webhookUrl' => 'https://good-event.test/api/payos/webhook',
            ],
        ]),
    ]);

    $data = app(PaymentService::class)->confirmWebhook('https://good-event.test/api/payos/webhook');

    expect($data['webhookUrl'])->toBe('https://good-event.test/api/payos/webhook');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api-merchant.payos.vn/confirm-webhook'
        && $request->hasHeader('x-client-id', 'client-id')
        && $request->hasHeader('x-api-key', 'api-key')
        && $request['webhookUrl'] === 'https://good-event.test/api/payos/webhook');
});

it('fails when PayOS rejects the webhook URL', function (): void {
    config()->set([
        'services.payos.base_url' => 'https://api-merchant.payos.vn',
        'services.payos.client_id' => 'client-id',
        'services.payos.api_key' => 'api-key',
        'services.payos.checksum_key' => 'checksum-key',
    ]);
    Http::fake([
        'https://api-merchant.payos.vn/confirm-webhook' => Http::response([
            'code' => '20',
            'desc' => 'Webhook URL invalid',
        ]),
    ]);

    expect(fn () => app(PaymentService::class)->confirmWebhook('https://invalid.test/webhook'))
        ->toThrow(RuntimeException::class, 'Webhook URL invalid');
});
