<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'translations' => __('client/settings.profile'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->safe()->except('avatar');

        $user->fill($validated);

        if (array_key_exists('bio', $validated)) {
            $bio = trim((string) ($validated['bio'] ?? ''));
            $user->bio = $bio === '' ? null : $bio;
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = Str::ulid() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('uploads/avatars', $filename, 'public');

            if ($user->getOriginal('avatar') && !Str::startsWith($user->getOriginal('avatar'), ['http://', 'https://'])) {
                Storage::disk('public')->delete($user->getOriginal('avatar'));
            }

            $user->avatar = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        Cache::forget("profile:client:v2:{$user->id}");
        Cache::forget("profile:partner:v2:{$user->id}");

        return to_route('profile.edit')->with('success', __('client/settings.profile.form.success'));
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
