<?php

namespace App\Http\Controllers\Auth;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PartnerProfile;
use App\Models\Location;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page for client.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Show the registration page for partner with provinces list.
     */
    public function createPartner(): Response
    {
        // note: provinces = locations where parent_id is null
        $provinces = Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('auth/PartnerRegister', [
            'provinces' => $provinces,
        ]);
    }

    /**
     * Handle an incoming registration request for client.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ])->assignRole(Role::CLIENT);

        event(new Registered($user));
        Auth::login($user);

        return to_route('login');
    }

    /**
     * Handle an incoming partner registration request.
     *
     */
    public function storePartner(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'identity_card_number' => 'required|string|max:50|unique:partner_profiles,identity_card_number',
            'ward_id' => 'required|integer|exists:locations,id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'ward_id.required' => 'Vui lòng chọn phường/xã',
            'ward_id.exists' => 'Phường/xã không hợp lệ',
        ]);

        $ward = Location::findOrFail($validated['ward_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ])->assignRole(Role::PARTNER);

        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_name' => $validated['name'],
            'identity_card_number' => $validated['identity_card_number'],
            'location_id' => $ward->id,
        ]);

        event(new Registered($user));
        return Inertia::location(route('filament.partner.auth.login'));
    }
}
