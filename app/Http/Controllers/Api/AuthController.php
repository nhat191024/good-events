<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PartnerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    public function registerPartner(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required',
            'country_code' => 'required',
            'avatar' => 'nullable',

            'partner_name' => 'required',
            'identity_card_number' => 'required',
            'location_id' => 'required|exists:locations,id',
        ]);

        DB::beginTransaction();

        try {
            // upload avatar
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // tạo user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'country_code' => $request->country_code,
                'avatar' => $avatarPath,
            ]);

            // tạo partner
            $partner = PartnerProfile::create([
                'user_id' => $user->id,
                'partner_name' => $request->partner_name,
                'identity_card_number' => $request->identity_card_number,
                'location_id' => $request->location_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Đăng ký partner thành công',
                'user' => $user,
                'partner' => $partner
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Lỗi server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
