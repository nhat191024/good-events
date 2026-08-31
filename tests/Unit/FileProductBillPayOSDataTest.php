<?php

use App\Enum\FileProductBillStatus;
use App\Filament\Admin\Resources\FileProductBills\Tables\FileProductBillsTable;
use App\Models\FileProductBill;
use App\Services\PaymentService;
use Illuminate\Contracts\Translation\Translator;

it('records PayOS request and response data on a file product bill', function (): void {
    $bill = Mockery::mock(FileProductBill::class)->makePartial();
    $bill->shouldReceive('save')->once()->andReturnTrue();

    $request = [
        'billId' => 123456789,
        'amount' => 110000,
    ];
    $response = [
        'paymentLinkId' => 'payment-link-id',
        'checkoutUrl' => 'https://pay.payos.vn/web/payment-link-id',
    ];

    $bill->recordPayOSPayment($request, $response);

    expect($bill->payos_order_code)->toBe(123456789)
        ->and($bill->payos_payment_link_id)->toBe('payment-link-id')
        ->and($bill->payos_data)->toBe([
            'request' => $request,
            'response' => $response,
        ]);
});

it('builds PayOS transaction metadata without removing existing metadata', function (): void {
    $request = [
        'billId' => 987654321,
        'amount' => 50000,
    ];
    $response = [
        'paymentLinkId' => 'wallet-payment-link-id',
        'checkoutUrl' => 'https://pay.payos.vn/web/wallet-payment-link-id',
    ];

    $metaData = PaymentService::withPayOSMetadata(
        ['reason' => 'Nạp tiền vào ví qua QR'],
        987654321,
        $request,
        $response,
    );

    expect($metaData['reason'])->toBe('Nạp tiền vào ví qua QR')
        ->and($metaData['payos'])->toBe([
            'order_code' => 987654321,
            'request' => $request,
            'payment_link_id' => 'wallet-payment-link-id',
            'response' => $response,
        ]);
});

it('offers only statuses different from the current bill status', function (): void {
    $translator = Mockery::mock(Translator::class);
    $translator->shouldReceive('get')->andReturnUsing(fn (string $key): string => $key);
    app()->instance('translator', $translator);

    $bill = new FileProductBill([
        'status' => FileProductBillStatus::PENDING,
    ]);

    $statuses = FileProductBillsTable::availableStatuses($bill);

    expect($statuses)->toHaveKeys([
        FileProductBillStatus::PAID->value,
        FileProductBillStatus::CANCELLED->value,
    ])->not->toHaveKey(FileProductBillStatus::PENDING->value);
});
