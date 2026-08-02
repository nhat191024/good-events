<?php

namespace App\Http\Requests;

use App\Models\Call;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = $this->user()?->id;
        $threadId = (int) $this->route('thread');

        if ($userId === null || $threadId < 1) {
            return false;
        }

        return Participant::query()
            ->where('thread_id', $threadId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $threadId = (int) $this->route('thread');

        return [
            'type' => ['required', 'string', Rule::in([Call::TYPE_AUDIO, Call::TYPE_VIDEO])],
            'invited_user_ids' => ['required', 'array', 'min:1'],
            'invited_user_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('participants', 'user_id')->where(
                    fn ($query) => $query
                        ->where('thread_id', $threadId)
                        ->whereNull('deleted_at')
                ),
                Rule::notIn([(int) $this->user()?->id]),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'invited_user_ids.required' => 'Vui lòng chọn ít nhất một người tham gia.',
            'invited_user_ids.min' => 'Vui lòng chọn ít nhất một người tham gia.',
            'invited_user_ids.*.exists' => 'Người được mời phải là thành viên của cuộc trò chuyện.',
            'invited_user_ids.*.not_in' => 'Người gọi không thể tự mời chính mình.',
            'invited_user_ids.*.distinct' => 'Danh sách người được mời không được trùng lặp.',
        ];
    }
}
