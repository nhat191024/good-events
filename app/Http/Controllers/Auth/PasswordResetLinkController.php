<?php

namespace App\Http\Controllers\Auth;

use App\Enum\CacheKey;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpMaxAttemptsException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\PasswordResetMailService;
use App\Services\PhoneLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function __construct(private PasswordResetMailService $passwordResetService) {}

    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
            'forgotStep' => $request->session()->pull('forgot_step'),
            'forgotCredential' => $request->session()->pull('forgot_credential'),
            'forgotMethod' => $request->session()->pull('forgot_method'),
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
            'email' => ['required', 'string'],
        ]);

        $input = $request->input('email');

        $phoneService = app(PhoneLoginService::class);
        if ($phoneService->isPhoneNumber($input)) {
            $input = $phoneService->findEmailByPhone($input) ?? $input;
        }

        $this->passwordResetService->sendResetLinkByEmail($input);

        return back()->with('status', __('Một đường dẫn đặt lại mật khẩu đã được gửi đến email của bạn nếu tài khoản tồn tại.'));
    }

    /**
     * Send OTP or email reset link for the multi-step forgot password flow.
     */
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'string', 'in:phone,email'],
            'credential' => ['required', 'string'],
        ]);

        $method = $request->input('method');
        $credential = $request->input('credential');

        $phoneService = app(PhoneLoginService::class);
        $user = $method === 'phone'
            ? User::where('phone', $phoneService->normalizePhone($credential))->first()
            : User::where('email', $credential)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'credential' => 'Không tìm thấy tài khoản với thông tin này.',
            ]);
        }

        try {
            if ($method === 'phone') {
                $user->sendPhoneVerificationNotification();
            } else {
                $this->passwordResetService->sendResetLinkByEmail($credential);
            }
        } catch (OtpMaxAttemptsException $e) {
            $hours = (int) $e->getMessage();
            throw ValidationException::withMessages([
                'credential' => "Bạn đã gửi quá nhiều lần. Vui lòng thử lại sau {$hours} giờ.",
            ]);
        } catch (OtpCooldownException $e) {
            $seconds = (int) $e->getMessage();
            throw ValidationException::withMessages([
                'credential' => "Vui lòng đợi {$seconds} giây trước khi gửi lại.",
            ]);
        }

        $request->session()->put('forgot_step', $method === 'email' ? 'email-sent' : 'phone-otp');
        $request->session()->put('forgot_method', $method);
        $request->session()->put('forgot_credential', $credential);

        return redirect()->route('password.request');
    }

    /**
     * Resend OTP for the phone verification step.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $phoneService = app(PhoneLoginService::class);
        $normalizedPhone = $phoneService->normalizePhone($request->input('phone'));
        $user = User::where('phone', $normalizedPhone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => 'Không tìm thấy tài khoản với số điện thoại này.',
            ]);
        }

        try {
            $user->sendPhoneVerificationNotification();
        } catch (OtpMaxAttemptsException $e) {
            $hours = (int) $e->getMessage();
            throw ValidationException::withMessages([
                'otp' => "Bạn đã gửi quá nhiều lần. Vui lòng thử lại sau {$hours} giờ.",
            ]);
        } catch (OtpCooldownException $e) {
            $seconds = (int) $e->getMessage();
            throw ValidationException::withMessages([
                'otp' => "Vui lòng đợi {$seconds} giây trước khi gửi lại.",
            ]);
        }

        $request->session()->put('forgot_step', 'phone-otp');
        $request->session()->put('forgot_method', 'phone');
        $request->session()->put('forgot_credential', $request->input('phone'));

        return redirect()->route('password.request');
    }

    /**
     * Verify the OTP for phone-based password reset.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $phoneService = app(PhoneLoginService::class);
        $normalizedPhone = $phoneService->normalizePhone($request->input('phone'));
        $user = User::where('phone', $normalizedPhone)->first();

        if (!$user || !app(OtpService::class)->verifyOtp($user->getPhoneForVerification(), $request->input('otp'))) {
            throw ValidationException::withMessages([
                'otp' => 'Mã OTP không đúng. Vui lòng kiểm tra lại.',
            ]);
        }

        $resetToken = Str::uuid()->toString();
        Cache::put(
            CacheKey::PASSWORD_RESET_TOKEN->value . hash('sha256', $resetToken),
            $user->id,
            now()->addMinutes(15)
        );

        $request->session()->put('forgot_reset_token', $resetToken);
        $request->session()->put('forgot_step', 'phone-reset');
        $request->session()->put('forgot_method', 'phone');
        $request->session()->put('forgot_credential', $request->input('phone'));

        return redirect()->route('password.request');
    }

    /**
     * Reset password using the session-stored token issued after OTP verification.
     */
    public function resetPhone(Request $request): RedirectResponse
    {
        ds($request->all());
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 12 ký tự.',
            'password.letters' => 'Mật khẩu phải chứa ít nhất một chữ cái.',
            'password.mixed' => 'Mật khẩu phải chứa cả chữ hoa và chữ thường.',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một chữ số.',
            'password.symbols' => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt.',
            'password.uncompromised' => 'Mật khẩu này đã bị lộ, vui lòng chọn mật khẩu khác.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $resetToken = $request->session()->pull('forgot_reset_token');

        if (!$resetToken) {
            throw ValidationException::withMessages([
                'password' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thực hiện lại từ đầu.',
            ]);
        }

        $cacheKey = CacheKey::PASSWORD_RESET_TOKEN->value . hash('sha256', $resetToken);
        $userId = Cache::pull($cacheKey);

        if (!$userId) {
            throw ValidationException::withMessages([
                'password' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thực hiện lại từ đầu.',
            ]);
        }

        $user = User::find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'password' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thực hiện lại từ đầu.',
            ]);
        }

        $this->passwordResetService->resetPassword($user, $request->input('password'));

        return redirect()->route('login')->with('status', 'Mật khẩu đã được đặt lại thành công.');
    }
}
