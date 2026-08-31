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
            'data.description' => ['sometimes', 'nullable', 'string'],
            'data.accountNumber' => ['sometimes', 'nullable', 'string'],
            'data.reference' => ['sometimes', 'nullable', 'string'],
            'data.transactionDateTime' => ['sometimes', 'nullable', 'string'],
            'data.currency' => ['sometimes', 'nullable', 'string'],
            'data.paymentLinkId' => ['required', 'string'],
            'data.code' => ['required', 'string'],
            'data.desc' => ['sometimes', 'nullable', 'string'],
            'data.counterAccountBankId' => ['sometimes', 'nullable', 'string'],
            'data.counterAccountBankName' => ['sometimes', 'nullable', 'string'],
            'data.counterAccountName' => ['sometimes', 'nullable', 'string'],
            'data.counterAccountNumber' => ['sometimes', 'nullable', 'string'],
            'data.virtualAccountName' => ['sometimes', 'nullable', 'string'],
            'data.virtualAccountNumber' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
