<?php

namespace App\Http\Requests;

use App\Models\Message;
use App\Support\ChatAttachmentType;
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
            'client_message_id' => ['nullable', 'uuid'],
            'type' => ['required', 'string', Rule::in([
                Message::TYPE_TEXT,
                Message::TYPE_IMAGE,
                Message::TYPE_FILE,
                Message::TYPE_VIDEO,
                Message::TYPE_LOCATION,
                Message::TYPE_PRICE_INCREASE_REQUEST,
            ])],
            'body' => [
                Rule::requiredIf($type === Message::TYPE_TEXT),
                'nullable',
                'string',
                'max:20000',
            ],
            'images' => [
                Rule::prohibitedIf(in_array($type, [Message::TYPE_FILE, Message::TYPE_VIDEO], true)),
                Rule::requiredIf($type === Message::TYPE_IMAGE),
                'array',
                'max:5',
            ],
            'images.*' => [
                'required',
                'image',
                'max:20480',
            ],
            'file' => [
                'bail',
                Rule::requiredIf($type === Message::TYPE_FILE),
                Rule::prohibitedIf($type !== Message::TYPE_FILE),
                'file',
                'max:20480',
                ChatAttachmentType::rule(),
            ],
            'video' => [
                'bail',
                Rule::requiredIf($type === Message::TYPE_VIDEO),
                Rule::prohibitedIf($type !== Message::TYPE_VIDEO),
                'file',
                'max:51200',
                ChatAttachmentType::rule(video: true),
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
            'requested_price' => [
                Rule::requiredIf($type === Message::TYPE_PRICE_INCREASE_REQUEST),
                'nullable',
                'integer',
                'min:1',
                'max:9000000000000000',
            ],
            'reason' => [
                Rule::requiredIf($type === Message::TYPE_PRICE_INCREASE_REQUEST),
                'nullable',
                'string',
                'max:1000',
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
            'images.*.max' => 'Mỗi ảnh không được vượt quá 20MB.',
            'images.prohibited' => 'Không thể gửi kèm ảnh trong tin nhắn file hoặc video.',
            'file.required' => 'Vui lòng chọn một file để gửi.',
            'file.file' => 'Vui lòng gửi đúng một file hợp lệ.',
            'file.max' => 'File không được vượt quá 20MB.',
            'file.prohibited' => 'Chỉ được gửi file trong tin nhắn loại file.',
            'file.uploaded' => 'Không thể tải file lên. Vui lòng kiểm tra dung lượng và thử lại.',
            'video.required' => 'Vui lòng chọn một video để gửi.',
            'video.file' => 'Vui lòng gửi đúng một video hợp lệ.',
            'video.max' => 'Video không được vượt quá 50MB.',
            'video.prohibited' => 'Chỉ được gửi video trong tin nhắn loại video.',
            'video.uploaded' => 'Không thể tải video lên. Vui lòng kiểm tra dung lượng và thử lại.',
            'location.latitude.required' => 'Vui lòng gửi vĩ độ.',
            'location.latitude.between' => 'Vĩ độ không hợp lệ.',
            'location.longitude.required' => 'Vui lòng gửi kinh độ.',
            'location.longitude.between' => 'Kinh độ không hợp lệ.',
            'requested_price.required' => 'Vui lòng nhập giá đề nghị.',
            'requested_price.integer' => 'Giá đề nghị phải là số nguyên.',
            'reason.required' => 'Vui lòng nhập lý do tăng giá.',
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
            'client_message_id' => $this->input('client_message_id'),
            'type' => $this->input('type', Message::TYPE_TEXT),
            'body' => $this->input('body'),
            'location_latitude' => data_get($location, 'latitude'),
            'location_longitude' => data_get($location, 'longitude'),
            'location_label' => data_get($location, 'label'),
            'location_address' => data_get($location, 'address'),
        ];
    }
}
