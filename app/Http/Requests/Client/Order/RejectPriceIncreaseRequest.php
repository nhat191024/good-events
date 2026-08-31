<?php

namespace App\Http\Requests\Client\Order;

use App\Models\PartnerBill;
use Illuminate\Foundation\Http\FormRequest;

class RejectPriceIncreaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof PartnerBill
            && (int) $order->client_id === (int) $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
