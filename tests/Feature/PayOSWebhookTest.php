<?php

use App\Enum\FileProductBillStatus;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use App\Services\PayOSWebhookService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

function payOSWebhookPayload(array $overrides = []): array
{
    $data = array_merge([
        'orderCode' => 123,
        'amount' => 3000,
        'description' => 'VQRIO123',
        'accountNumber' => '12345678',
        'reference' => 'TF230204212323',
        'transactionDateTime' => '2023-02-04 18:25:00',
        'currency' => 'VND',
        'paymentLinkId' => '124c33293c43417ab7879e14c8d9eb18',
        'code' => '00',
        'desc' => 'Thành công',
        'counterAccountBankId' => '',
        'counterAccountBankName' => '',
        'counterAccountName' => '',
        'counterAccountNumber' => '',
        'virtualAccountName' => '',
        'virtualAccountNumber' => '',
    ], $overrides);

    return [
        'code' => '00',
        'desc' => 'success',
        'success' => true,
        'data' => $data,
        'signature' => 'signed-webhook',
    ];
}

it('rejects a webhook when PayOS signature verification fails', function (): void {
    $this->mock(PaymentService::class)
        ->shouldReceive('verifyWebhook')
        ->once()
        ->andThrow(new RuntimeException('Invalid signature'));

    $this->postJson(route('payos.webhook'), payOSWebhookPayload())
        ->assertBadRequest()
        ->assertJsonPath('success', false);
});

it('acknowledges a signed PayOS verification sample with an unknown order', function (): void {
    $payload = payOSWebhookPayload();

    $this->mock(PaymentService::class)
        ->shouldReceive('verifyWebhook')
        ->once()
        ->andReturn($payload['data']);
    $this->mock(PayOSWebhookService::class)
        ->shouldReceive('handle')
        ->once()
        ->with(true, '00', $payload['data'])
        ->andReturn('Webhook acknowledged for an unknown order.');

    $this->postJson(route('payos.webhook'), $payload)
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Webhook acknowledged for an unknown order.');
});

it('marks a matching file product bill as paid idempotently', function (): void {
    activity()->disableLogging();

    Schema::create('file_product_bills', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('file_product_id');
        $table->unsignedBigInteger('client_id');
        $table->double('total');
        $table->double('tax')->nullable();
        $table->double('final_total')->nullable();
        $table->string('status');
        $table->string('payment_method');
        $table->unsignedBigInteger('payos_order_code')->nullable()->unique();
        $table->string('payos_payment_link_id')->nullable();
        $table->json('payos_data')->nullable();
        $table->timestamps();
    });
    $bill = FileProductBill::query()->create([
        'file_product_id' => 1,
        'client_id' => 1,
        'total' => 100000,
        'tax' => 10000,
        'final_total' => 110000,
        'status' => FileProductBillStatus::PENDING,
        'payment_method' => 'qr_transfer',
        'payos_order_code' => 456789,
        'payos_payment_link_id' => 'file-payment-link',
        'payos_data' => ['request' => ['billId' => 456789]],
    ]);
    $data = payOSWebhookPayload([
        'orderCode' => 456789,
        'amount' => 110000,
        'paymentLinkId' => 'file-payment-link',
    ])['data'];
    $service = app(PayOSWebhookService::class);

    $service->handle(true, '00', $data);
    $service->handle(true, '00', $data);

    $bill->refresh();

    expect($bill->status)->toBe(FileProductBillStatus::PAID)
        ->and($bill->payos_data['webhook'])->toBe($data);

    Schema::drop('file_product_bills');
    activity()->enableLogging();
});
