<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    private PasswordResetMailService $passwordResetService;

    public function __construct(PasswordResetMailService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $this->passwordResetService->sendResetLinkByEmail($email);

        return back()->with('status', __('Một đường dẫn đặt lại mật khẩu đã được gửi đến email của bạn nếu tài khoản tồn tại.'));
    }
}
