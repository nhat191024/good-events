<?php

namespace App\Http\Requests\Partner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePartnerServiceRequest extends FormRequest
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
            'category_id' => [
                'required',
                'exists:partner_categories,id',
                Rule::unique('partner_services', 'category_id')
                    ->where('user_id', auth()->id()),
            ],
            'service_media' => ['nullable', 'array'],
            'service_media.*.name' => ['required', 'string', 'max:255'],
            'service_media.*.url' => ['required', 'url', 'max:2048'],
            'service_media.*.description' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục dịch vụ.',
            'category_id.exists' => 'Danh mục dịch vụ không tồn tại.',
            'category_id.unique' => 'Bạn đã đăng ký dịch vụ này rồi.',
            'service_media.array' => 'Danh sách media không hợp lệ.',
            'service_media.*.name.required' => 'Vui lòng nhập tên video.',
            'service_media.*.name.string' => 'Tên video phải là chuỗi ký tự.',
            'service_media.*.url.required' => 'Vui lòng nhập đường dẫn video.',
            'service_media.*.url.url' => 'Đường dẫn video không hợp lệ.',
            'service_media.*.description.required' => 'Vui lòng nhập mô tả video.',
        ];
    }
}
