<?php

namespace App\Http\Requests;

use App\Models\PushDevice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPushDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', Rule::in([
                PushDevice::PLATFORM_IOS,
                PushDevice::PLATFORM_ANDROID,
            ])],
            'fcm_token' => ['nullable', 'string', 'max:512', 'required_without:voip_token'],
            'voip_token' => [
                'nullable',
                'string',
                'max:512',
                'required_without:fcm_token',
                'prohibited_unless:platform,'.PushDevice::PLATFORM_IOS,
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fcm_token.required_without' => 'Cần ít nhất một FCM token hoặc VoIP token.',
            'voip_token.required_without' => 'Cần ít nhất một FCM token hoặc VoIP token.',
            'voip_token.prohibited_unless' => 'VoIP token chỉ hợp lệ với thiết bị iOS.',
        ];
    }
}
