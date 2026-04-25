<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'country_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:25'],
            'bio' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', File::image()->max(5 * 1024)],
        ];

        if ($this->user()->hasRole(\App\Enum\Role::PARTNER)) {
            $rules = array_merge($rules, [
                'partner_name' => ['nullable', 'string', 'max:255'],
                'video_url' => ['nullable', 'string', 'max:2048'],
                'identity_card_number' => ['nullable', 'string', 'max:50'],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'selfie_image' => ['nullable', File::image()->max(5 * 1024)],
                'front_identity_card_image' => ['nullable', File::image()->max(5 * 1024)],
                'back_identity_card_image' => ['nullable', File::image()->max(5 * 1024)],
            ]);
        }

        return $rules;
    }
}
