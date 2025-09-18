<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enum\Role;

class ProfileSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole(Role::PARTNER);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            // User validation rules
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'country_code' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],

            // Partner profile validation rules
            'partner_name' => ['required', 'string', 'max:255'],
            'identity_card_number' => ['required', 'string', 'max:20'],
            'location_id' => ['required', 'exists:locations,id'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên đầy đủ là bắt buộc.',
            'name.max' => 'Tên đầy đủ không được vượt quá 255 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'partner_name.required' => 'Tên đối tác/doanh nghiệp là bắt buộc.',
            'partner_name.max' => 'Tên đối tác không được vượt quá 255 ký tự.',
            'identity_card_number.required' => 'Số chứng minh thư là bắt buộc.',
            'identity_card_number.max' => 'Số chứng minh thư không được vượt quá 20 ký tự.',
            'location_id.required' => 'Vị trí là bắt buộc.',
            'location_id.exists' => 'Vị trí được chọn không hợp lệ.',
        ];
    }
}
