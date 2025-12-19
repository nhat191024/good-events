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

        $validated = $request->validate([
            'identity_card_number' => 'required|string|min:12|max:50|unique:partner_profiles,identity_card_number',
            'ward_id' => 'required|integer|exists:locations,id',
        ], [
            'identity_card_number.required' => 'Vui lòng nhập số CMND/CCCD',
            'identity_card_number.string' => 'Số CMND/CCCD không hợp lệ',
            'identity_card_number.min' => 'Số CMND/CCCD phải có ít nhất :min ký tự',
            'identity_card_number.max' => 'Số CMND/CCCD không được vượt quá :max ký tự',
            'identity_card_number.unique' => 'Số CMND/CCCD đã được sử dụng',
            'ward_id.required' => 'Vui lòng chọn phường/xã',
            'ward_id.integer' => 'Phường/xã không hợp lệ',
            'ward_id.exists' => 'Phường/xã không hợp lệ',
        ]);

        $ward = Location::findOrFail($validated['ward_id']);

        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_name' => $user->name,
            'identity_card_number' => $validated['identity_card_number'],
            'location_id' => $ward->id,
        ]);

        $roleId = \Spatie\Permission\Models\Role::where('name', Role::PARTNER->value)->value('id');

        $user->removeRole(Role::CLIENT);

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => 'App\Models\Partner',
            'model_id' => $user->id,
        ]);

        $user->save();

        return Inertia::location(route('filament.partner.pages.profile-settings'));
    }
}
