<?php

namespace App\Http\Requests;

use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;

class AccessThreadCallRequest extends FormRequest
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
        return [];
    }
}
