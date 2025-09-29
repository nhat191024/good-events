<?php

namespace App\Services;

use PayOS\PayOS;

class PaymentService
{
    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            env("PAYOS_CLIENT_ID"),
            env("PAYOS_API_KEY"),
            env("PAYOS_CHECKSUM_KEY")
        );
    }

    /**
     * Process appointment payment
     *
     * @param array $data
     * @param string $paymentMethod
     * @param bool $isAppRequest
     * @return array
     */
    public function processAppointmentPayment(array $data, $paymentMethod, bool $isAppRequest)
    {
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
                    $data['billId'],
                    $data['amount'],
                    $data['buyerName'] ?? null,
                    $data['buyerEmail'] ?? null,
                    $data['buyerPhone'] ?? null,
                    $data['items'] ?? null,
                    $data['expiryTime'] ?? null,
                    $isAppRequest,
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
        //TODO: Implement wallet payment logic
        return [];
    }

    /**
     * Process credit card payment
     */
    private function processCreditCardPayment($appointment)
    {
        //TODO: Implement credit card payment logic
        return [];
    }

    /**
     * Process QR transfer payment
     *
     * @param int $billId
     * @param string $billCode
     * @param int $amount
     * @param string|null $buyerName
     * @param string|null $buyerEmail
     * @param string|null $buyerPhone
     * @param string|null $buyerAddress
     * @param array|null $items
     * @param int|null $expiryTime
     * @param bool $isAppRequest
     *
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
        bool $isAppRequest,
    ) {
        $expiryTime ??= intval(now()->addMinutes(5)->timestamp);

        $url = null;
        if ($isAppRequest) {
            $url = env("APP_PAYMENT_RESULT_DEEPLINK_URL");
        } else {
            $url = route('payment.result');
        }

        $paymentRequest = [
            'orderCode' => $billId,
            'amount' => $amount,
            'description' => "VQR-{$billCode}",
            'buyerName' => $buyerName,
            'buyerEmail' => $buyerEmail,
            'buyerPhone' => $buyerPhone,
            'items' => $items,
            'cancelUrl' => $url,
            'returnUrl' => $url,
            'expiredAt' => $expiryTime,
        ];

        $signature = self::createSignaturePaymentRequest(
            env("PAYOS_CHECKSUM_KEY"),
            $paymentRequest
        );

        $paymentRequest['signature'] = $signature;

        ds($paymentRequest);

        $response = $this->payOS->createPaymentLink($paymentRequest);

        $response['url'] = $url;

        return $response;
    }

    /**
     * Create a signature for the payment request
     *
     * @param string $checksumKey
     * @param array $obj
     * @return string
     */
    public static function createSignatureFromObj($checksumKey, $obj)
    {
        ksort($obj);
        $queryStrArr = [];
        foreach ($obj as $key => $value) {
            if (in_array($value, ["undefined", "null"]) || gettype($value) == "NULL") {
                $value = "";
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
        $queryStr = implode("&", $queryStrArr);
        $signature = hash_hmac('sha256', $queryStr, $checksumKey);
        return $signature;
    }

    private static function createSignaturePaymentRequest($checksumKey, $obj)
    {
        $dataStr = "amount={$obj["amount"]}&cancelUrl={$obj["cancelUrl"]}&description={$obj["description"]}&orderCode={$obj["orderCode"]}&returnUrl={$obj["returnUrl"]}";
        $signature = hash_hmac("sha256", $dataStr, $checksumKey);
        return $signature;
    }
}
