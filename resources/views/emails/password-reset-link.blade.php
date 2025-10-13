<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333; margin: 0;">
            @if($locale === 'en')
                Reset Your Password
            @else
                Đặt lại mật khẩu
            @endif
        </h1>
    </div>

    <!-- Content -->
    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        @if($locale === 'en')
            <p style="color: #666; line-height: 1.6;">
                Hello {{ $user->name }},
            </p>
            <p style="color: #666; line-height: 1.6;">
                You requested a password reset for your account. Click the button below to reset your password. This link will expire in 60 minutes.
            </p>
        @else
            <p style="color: #666; line-height: 1.6;">
                Xin chào {{ $user->name }},
            </p>
            <p style="color: #666; line-height: 1.6;">
                Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình. Nhấp vào nút bên dưới để đặt lại mật khẩu. Liên kết này sẽ hết hạn trong 60 phút.
            </p>
        @endif
    </div>

    <!-- CTA Button -->
    <div style="text-align: center; margin-bottom: 30px;">
        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
            @if($locale === 'en')
                Reset Password
            @else
                Đặt lại mật khẩu
            @endif
        </a>
    </div>

    <!-- Alternative Link -->
    <div style="background-color: #f0f0f0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <p style="color: #999; font-size: 12px; margin: 0;">
            @if($locale === 'en')
                Or copy and paste this link into your browser:
            @else
                Hoặc sao chép và dán liên kết này vào trình duyệt của bạn:
            @endif
        </p>
        <p style="color: #007bff; font-size: 12px; word-break: break-all; margin: 5px 0 0 0;">
            {{ $resetUrl }}
        </p>
    </div>

    <!-- Security Notice -->
    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <p style="color: #856404; font-size: 13px; margin: 0;">
            @if($locale === 'en')
                <strong>Security Notice:</strong> If you did not request this password reset, please ignore this email or contact support immediately.
            @else
                <strong>Thông báo bảo mật:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu này, vui lòng bỏ qua email này hoặc liên hệ với bộ phận hỗ trợ ngay lập tức.
            @endif
        </p>
    </div>

    <!-- Footer -->
    <div style="text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px;">
        <p style="margin: 5px 0;">
            @if($locale === 'en')
                © {{ date('Y') }} Good Events. All rights reserved.
            @else
                © {{ date('Y') }} Good Events. Tất cả quyền được bảo lưu.
            @endif
        </p>
    </div>
</div>