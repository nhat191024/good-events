<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333; margin: 0;">
            @if ($locale === 'en')
                Verify Your Email
            @else
                Xác thực Email
            @endif
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        @if ($locale === 'en')
            <p style="color: #666; line-height: 1.6;">Hello {{ $user->name }},</p>
            <p style="color: #666; line-height: 1.6;">Please confirm your email address by clicking the button below.
                This link will expire soon.</p>
        @else
            <p style="color: #666; line-height: 1.6;">Xin chào {{ $user->name }},</p>
            <p style="color: #666; line-height: 1.6;">Vui lòng xác thực địa chỉ email của bạn bằng cách nhấn nút bên
                dưới. Liên kết sẽ hết hạn sau một thời gian.</p>
        @endif
    </div>

    <div style="text-align: center; margin-bottom: 30px;">
        <a href="{{ $verifyUrl }}" target="_blank"
            style="display: inline-block; padding: 12px 30px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
            @if ($locale === 'en')
                Verify Email
            @else
                Xác thực Email
            @endif
        </a>
    </div>

    <div style="background-color: #f0f0f0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <p style="color: #999; font-size: 12px; margin: 0;">
            @if ($locale === 'en')
                Or copy and paste this link into your browser:
            @else
                Hoặc sao chép và dán liên kết này vào trình duyệt:
            @endif
        </p>
        <p style="color: #2563eb; font-size: 12px; word-break: break-all; margin: 5px 0 0 0;">{{ $verifyUrl }}</p>
    </div>

    <div
        style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <p style="color: #856404; font-size: 13px; margin: 0;">
            @if ($locale === 'en')
                <strong>Security Notice:</strong> If you did not create an account, please ignore this email.
            @else
                <strong>Thông báo bảo mật:</strong> Nếu bạn không tạo tài khoản, hãy bỏ qua email này.
            @endif
        </p>
    </div>

    <div style="text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px;">
        <p style="margin: 5px 0;">© {{ date('Y') }} Good Events.</p>
    </div>
</div>
