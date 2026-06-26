<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type', Message::TYPE_TEXT),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->input('type', Message::TYPE_TEXT);

        return [
            'type' => ['required', 'string', Rule::in([
                Message::TYPE_TEXT,
                Message::TYPE_IMAGE,
                Message::TYPE_LOCATION,
            ])],
            'body' => [
                Rule::requiredIf($type === Message::TYPE_TEXT),
                'nullable',
                'string',
                'max:5000',
            ],
            'images' => [
                Rule::requiredIf($type === Message::TYPE_IMAGE),
                'array',
                'max:5',
            ],
            'images.*' => [
                'required',
                'image',
                'max:5120',
            ],
            'location.latitude' => [
                Rule::requiredIf($type === Message::TYPE_LOCATION),
                'numeric',
                'between:-90,90',
            ],
            'location.longitude' => [
                Rule::requiredIf($type === Message::TYPE_LOCATION),
                'numeric',
                'between:-180,180',
            ],
            'location.label' => [
                'nullable',
                'string',
                'max:255',
            ],
            'location.address' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'images.required' => 'Vui lòng chọn ít nhất một ảnh.',
            'images.max' => 'Bạn chỉ có thể gửi tối đa 5 ảnh trong một tin nhắn.',
            'images.*.image' => 'File gửi lên phải là ảnh.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 5MB.',
            'location.latitude.required' => 'Vui lòng gửi vĩ độ.',
            'location.latitude.between' => 'Vĩ độ không hợp lệ.',
            'location.longitude.required' => 'Vui lòng gửi kinh độ.',
            'location.longitude.between' => 'Kinh độ không hợp lệ.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messageAttributes(int $threadId, int $userId): array
    {
        $location = $this->input('location', []);

        return [
            'thread_id' => $threadId,
            'user_id' => $userId,
            'type' => $this->input('type', Message::TYPE_TEXT),
            'body' => $this->input('body'),
            'location_latitude' => data_get($location, 'latitude'),
            'location_longitude' => data_get($location, 'longitude'),
            'location_label' => data_get($location, 'label'),
            'location_address' => data_get($location, 'address'),
        ];
    }
}
