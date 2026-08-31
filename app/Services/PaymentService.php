<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use PayOS\PayOS;
use RuntimeException;

class PaymentService
{
    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key'),
            config('services.payos.partner_code')
        );
    }

    /**
     * @param  array<string, mixed>  $metaData
     * @param  array<string, mixed>  $paymentRequest
     * @param  array<string, mixed>|null  $paymentResponse
     * @return array<string, mixed>
     */
    public static function withPayOSMetadata(
        array $metaData,
        int $orderCode,
        array $paymentRequest,
        ?array $paymentResponse = null,
    ): array {
        $metaData['payos'] = [
            'order_code' => $orderCode,
            'request' => $paymentRequest,
        ];

        if ($paymentResponse !== null) {
            $metaData['payos']['payment_link_id'] = $paymentResponse['paymentLinkId'] ?? null;
            $metaData['payos']['response'] = $paymentResponse;
        }

        return $metaData;
    }

    /**
     * @param  array<string, mixed>  $webhookBody
     * @return array<string, mixed>
     */
    public function verifyWebhook(array $webhookBody): array
    {
        return $this->payOS->verifyPaymentWebhookData($webhookBody);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmWebhook(string $webhookUrl): array
    {
        $response = Http::baseUrl(config('services.payos.base_url'))
            ->acceptJson()
            ->withHeaders([
                'x-client-id' => config('services.payos.client_id'),
                'x-api-key' => config('services.payos.api_key'),
            ])
            ->post('/confirm-webhook', [
                'webhookUrl' => $webhookUrl,
            ]);

        $response->throw();
        $body = $response->json();

        if (($body['code'] ?? null) !== '00') {
            throw new RuntimeException($body['desc'] ?? 'PayOS could not confirm the webhook URL.');
        }

        return $body['data'] ?? [];
    }

    /**
     * Process appointment payment
     *
     * @param  string  $paymentMethod
     * @return array
     */
    public function processAppointmentPayment(
        array $data,
        $paymentMethod,
        bool $isAppRequest,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
    ) {
        // This would integrate with your payment gateway
        // For now, we'll simulate payment processing

        switch ($paymentMethod) {
            case 'wallet':
                return $this->processWalletPayment($data);
            case 'credit_card':
                return $this->processCreditCardPayment($data);
            case 'qr_transfer':
                return $this->processQRTransferPayment(
                    $data['billId'],
                    $data['billCode'],
                    $data['amount'],
                    $data['buyerName'] ?? null,
                    $data['buyerEmail'] ?? null,
                    $data['buyerPhone'] ?? null,
                    $data['items'] ?? null,
                    $data['expiryTime'] ?? null,
                    $isAppRequest,
                    $returnUrl,
                    $cancelUrl,
                );
            default:
                throw new \Exception('Invalid payment method');
        }
    }

    /**
     * Process wallet payment
     */
    private function processWalletPayment($appointment)
    {
        // TODO: Implement wallet payment logic
        return [];
    }

    /**
     * Process credit card payment
     */
    private function processCreditCardPayment($appointment)
    {
        // TODO: Implement credit card payment logic
        return [];
    }

    /**
     * Process QR transfer payment
     *
     * @param  string|null  $buyerAddress
     * @return array
     */
    private function processQRTransferPayment(
        int $billId,
        string $billCode,
        int $amount,
        ?string $buyerName = null,
        ?string $buyerEmail = null,
        ?string $buyerPhone = null,
        ?array $items = null,
        ?int $expiryTime = null,
        ?bool $isAppRequest = false,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
    ) {
        $expiryTime ??= intval(now()->addMinutes(5)->timestamp);

        $url = $returnUrl;
        if (! $url) {
            $url = $isAppRequest
                ? config('services.payos.app_deep_link')
                : route('payment.result');
        }

        $cancelTarget = $cancelUrl;
        if (! $cancelTarget) {
            $cancelTarget = $isAppRequest
                ? config('services.payos.app_deep_link')
                : route('payment.result');
        }

        $paymentRequest = [
            'orderCode' => $billId,
            'amount' => $amount,
            'description' => "VQR-{$billCode}",
            'buyerName' => $buyerName,
            'buyerEmail' => $buyerEmail,
            'buyerPhone' => $buyerPhone,
            'items' => $items,
            'cancelUrl' => $cancelTarget,
            'returnUrl' => $url,
            'expiredAt' => $expiryTime,
        ];

        $signature = self::createSignaturePaymentRequest(
            env('PAYOS_CHECKSUM_KEY'),
            $paymentRequest
        );

        $paymentRequest['signature'] = $signature;

        $response = $this->payOS->createPaymentLink($paymentRequest);

        $response['url'] = $url;

        return $response;
    }

    /**
     * Create a signature for the payment request
     *
     * @param  string  $checksumKey
     * @param  array  $obj
     * @return string
     */
    public static function createSignatureFromObj($checksumKey, $obj)
    {
        ksort($obj);
        $queryStrArr = [];
        foreach ($obj as $key => $value) {
            if (in_array($value, ['undefined', 'null']) || gettype($value) == 'NULL') {
                $value = '';
            }

            if (is_array($value)) {
                $valueSortedElementObj = array_map(function ($ele) {
                    ksort($ele);

                    return $ele;
                }, $value);
                $value = json_encode($valueSortedElementObj);
            }
            $queryStrArr[] = "{$key}={$value}";
        }
        $queryStr = implode('&', $queryStrArr);
        $signature = hash_hmac('sha256', $queryStr, $checksumKey);

        return $signature;
    }

    private static function createSignaturePaymentRequest($checksumKey, $obj)
    {
        $dataStr = "amount={$obj['amount']}&cancelUrl={$obj['cancelUrl']}&description={$obj['description']}&orderCode={$obj['orderCode']}&returnUrl={$obj['returnUrl']}";
        $signature = hash_hmac('sha256', $dataStr, $checksumKey);

        return $signature;
    }
}
