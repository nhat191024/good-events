<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class UploadServiceImagesRequest extends FormRequest
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
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Vui lòng chọn ít nhất 1 ảnh.',
            'images.array' => 'Dữ liệu ảnh không hợp lệ.',
            'images.min' => 'Vui lòng chọn ít nhất 1 ảnh.',
            'images.max' => 'Tối đa 10 ảnh mỗi lần tải lên.',
            'images.*.required' => 'File ảnh không được trống.',
            'images.*.image' => 'File phải là ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif hoặc webp.',
            'images.*.max' => 'Kích thước mỗi ảnh tối đa 5MB.',
        ];
    }
}
