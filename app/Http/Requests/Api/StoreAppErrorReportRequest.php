<?php

namespace App\Http\Requests\Api;

use App\Enum\AppErrorSeverity;
use App\Enum\AppErrorType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppErrorReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('api_method')) {
            $this->merge([
                'api_method' => strtoupper((string) $this->input('api_method')),
            ]);
        }
    }

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
            'type' => ['required', Rule::enum(AppErrorType::class)],
            'severity' => ['sometimes', Rule::enum(AppErrorSeverity::class)],
            'custom_type' => ['nullable', 'required_if:type,other', 'string', 'max:255'],
            'error_code' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'source' => ['nullable', 'string', 'max:1000'],
            'stack_trace' => ['nullable', 'string', 'max:100000'],
            'context' => ['nullable', 'array'],
            'api_method' => ['nullable', 'required_if:type,api', 'string', Rule::in(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])],
            'api_url' => ['nullable', 'required_if:type,api', 'string', 'max:5000'],
            'api_status_code' => ['nullable', 'integer', 'between:100,599'],
            'api_request' => ['nullable', 'array'],
            'api_response' => ['nullable', 'array'],
            'app_version' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:50'],
            'os_version' => ['nullable', 'string', 'max:100'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Loại lỗi là bắt buộc.',
            'type.enum' => 'Loại lỗi không hợp lệ.',
            'custom_type.required_if' => 'Vui lòng mô tả loại lỗi khác.',
            'message.required' => 'Nội dung lỗi là bắt buộc.',
            'api_method.required_if' => 'HTTP method là bắt buộc đối với lỗi API.',
            'api_url.required_if' => 'URL là bắt buộc đối với lỗi API.',
        ];
    }
}
