<?php

namespace App\Http\Requests\Client\OrderHistory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PartnerBill;

class CancelOrderRequest extends FormRequest
{
    // todo: enable this after hot fix
    // public function authorize(): bool
    // {
    //     $orderId = $this->input('order_id');
    //     $bill = PartnerBill::find($orderId);

    //     if (!$bill) {
    //         return false;
    //     }

    //     return $bill->user_id === $this->user()?->id;
    // }

    public function rules(): array
    {
        \Log::debug('CancelOrderRequest data', $this->all());
        return [
            'order_id' => ['required', 'integer', 'exists:partner_bills,id'],
            // 'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Thiếu ID của đơn hàng cần hủy.',
            'order_id.exists' => 'Không tìm thấy đơn hàng tương ứng.',
            // 'reason.max' => 'Lý do hủy không được vượt quá 500 ký tự.',
        ];
    }
}
