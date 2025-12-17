<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Enum\Role;

use App\Models\User;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\PartnerProfile;
use App\Models\Location;

use App\Services\EmailVerificationMailService;

use App\Settings\PartnerSettings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $user = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(Role::CLIENT);

        app(EmailVerificationMailService::class)->sendVerificationLink($user);
        Auth::login($user);

        return redirect()->route('verification.notice');
        // return to_route('login');
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

        $user = Partner::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country_code' => '+84',
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($validated['name']) . '&size=512',
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole(Role::PARTNER);

        $defaultBalance = app(PartnerSettings::class)->default_balance;
        $meta = [
            'reason' => __('admin/partner.messages.app_deposit'),
            'old_balance' => $user->balanceInt,
            'new_balance' => $user->balanceInt + $defaultBalance,
        ];
        $user->deposit($defaultBalance, $meta)->save();

        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_name' => $validated['name'],
            'identity_card_number' => $validated['identity_card_number'],
            'location_id' => $ward->id,
        ]);

        app(EmailVerificationMailService::class)->sendVerificationLink($user);
        Auth::login($user);
        return Inertia::location(route('filament.partner.pages.dashboard'));
    }

    /**
     * Show the partner registration page for existing clients.
     */
    public function createPartnerFromClient(Request $request): Response
    {
        $user = $request->user();
        $isPartner = $user->hasRole(Role::PARTNER->value);

        // note: provinces = locations where parent_id is null
        $provinces = Location::query()
            ->whereNull('parent_id')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('settings/RegisterPartner', [
            'provinces' => $provinces,
            'isPartner' => $isPartner,
            'partnerDashboardUrl' => route('filament.partner.pages.profile-settings'),
        ]);
    }

    /**
     * Handle the partner upgrade request for existing clients.
     */
    public function makePartner(Request $request)
    {
        $user = $request->user();

        // Check if user already has partner role to avoid duplicates/errors?
        // Assuming the UI hides the link if they are already a partner, but good to be safe.
        // However, user just asked for the logic. I'll stick to the core requirement.

        $validated = $request->validate([
            'identity_card_number' => 'required|string|max:50|unique:partner_profiles,identity_card_number',
            'ward_id' => 'required|integer|exists:locations,id',
        ], [
            'ward_id.required' => 'Vui lòng chọn phường/xã',
            'ward_id.exists' => 'Phường/xã không hợp lệ',
        ]);

        $ward = Location::findOrFail($validated['ward_id']);

        $user->assignRole(Role::PARTNER);

        // Update model_type in model_has_roles to Partner
        // existing storePartner logic updates where model_type is User::class.
        // assignRole adds a row with User::class.
        // We want to update ONLY the new row, or just generally ensure the Partner role row has the right model_type.
        // Since we just assigned it, it should be there.

        // To be safe and target correctly:
        // We can look for the role ID of PARTNER.
        $partnerRole = \Spatie\Permission\Models\Role::findByName(Role::PARTNER->value); // Assuming Role::PARTNER is the name string

        DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('role_id', $partnerRole->id)
            ->where('model_type', User::class)
            ->update(['model_type' => Partner::class]);

        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_name' => $user->name,
            'identity_card_number' => $validated['identity_card_number'],
            'location_id' => $ward->id,
        ]);

        return Inertia::location(route('filament.partner.pages.profile-settings'));
    }
}
