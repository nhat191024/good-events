<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PayOSWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'desc' => ['required', 'string'],
            'success' => ['required', 'boolean'],
            'signature' => ['required', 'string'],
            'data' => ['required', 'array'],
            'data.orderCode' => ['required', 'integer', 'min:1'],
            'data.amount' => ['required', 'integer', 'min:0'],
            'data.description' => ['required', 'string'],
            'data.accountNumber' => ['required', 'string'],
            'data.reference' => ['required', 'string'],
            'data.transactionDateTime' => ['required', 'date_format:Y-m-d H:i:s'],
            'data.currency' => ['required', 'string'],
            'data.paymentLinkId' => ['required', 'string'],
            'data.code' => ['required', 'string'],
            'data.desc' => ['required', 'string'],
            'data.counterAccountBankId' => ['present', 'nullable', 'string'],
            'data.counterAccountBankName' => ['present', 'nullable', 'string'],
            'data.counterAccountName' => ['present', 'nullable', 'string'],
            'data.counterAccountNumber' => ['present', 'nullable', 'string'],
            'data.virtualAccountName' => ['present', 'nullable', 'string'],
            'data.virtualAccountNumber' => ['present', 'nullable', 'string'],
        ];
    }
}
