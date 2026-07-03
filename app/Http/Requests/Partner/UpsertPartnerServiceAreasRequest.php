<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPartnerServiceAreasRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_ids' => ['present', 'array'],
            'location_ids.*' => [
                'integer',
                Rule::exists('locations', 'id')
                    ->whereNotNull('parent_id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'location_ids.present' => 'Vui lòng cung cấp danh sách khu vực dịch vụ.',
            'location_ids.array' => 'Danh sách khu vực dịch vụ không hợp lệ.',
            'location_ids.*.integer' => 'Mỗi ID khu vực dịch vụ phải là một số nguyên.',
            'location_ids.*.exists' => 'Một hoặc nhiều ID khu vực dịch vụ không tồn tại hoặc không hợp lệ.',
        ];
    }
}
