<?php

namespace App\Http\Requests;

use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteChatUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = $this->user()?->id;
        $threadId = (int) $this->route('thread');

        return $userId !== null && Participant::query()
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                Rule::notIn([(int) $this->user()?->id]),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Vui lòng chọn người dùng cần mời.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'user_id.not_in' => 'Bạn không thể tự mời chính mình.',
        ];
    }
}
