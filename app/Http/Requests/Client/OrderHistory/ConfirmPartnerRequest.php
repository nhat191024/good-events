<?php

namespace App\Http\Requests\Client\OrderHistory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PartnerBill;
use Illuminate\Support\Facades\Log;

class ConfirmPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        Log::debug('[request file] authorising confirm choose partner requrest', $this->all());
        $orderId = $this->input('order_id');
        $bill = PartnerBill::find($orderId);

        if (!$bill) {
            return false;
        }

        return $bill->client_id === $this->user()?->id;
    }

    public function rules(): array
    {
        Log::debug('[request file] ConfirmPartnerRequest data', $this->all());
        return [
            'order_id' => ['required', 'integer', 'exists:partner_bills,id'],
            'partner_id' => ['required', 'integer', 'exists:users,id'],
            // 'voucher_code' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Thiếu ID của đơn hàng cần hủy.',
            'order_id.exists' => 'Không tìm thấy đơn hàng tương ứng.',
            'partner_id.required' => 'Thiếu ID của đối tác cần hủy.',
            'partner_id.exists' => 'Không tìm thấy đối tác tương ứng.',
        ];
    }
}
