<?php

namespace App\Http\Controllers\Api;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterClientRequest;
use App\Models\User;
use App\Services\EmailVerificationMailService;
use Illuminate\Http\JsonResponse;

class AuthApiController extends Controller
{
    public function registerClient(RegisterClientRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $user->assignRole(Role::CLIENT->value);

        $token = $user->createToken('auth_token')->plainTextToken;

        dispatch(function () use ($user) {
            app(EmailVerificationMailService::class)->sendVerificationLink($user);
        })->afterResponse();

        return response()->json([
            'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác nhận tài khoản.',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => Role::CLIENT->value,
            ],
        ], 201);
    }
}