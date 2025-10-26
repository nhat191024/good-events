<?php

return [
    'profile' => [
        'breadcrumb' => 'Cài đặt hồ sơ',
        'head_title' => 'Cài đặt hồ sơ',
        'heading' => [
            'title' => 'Thông tin hồ sơ',
            'description' => 'Cập nhật thông tin hồ sơ và dữ liệu liên hệ của bạn',
        ],
        'form' => [
            'partner_name_label' => 'Tên đối tác',
            'sections' => [
                'avatar' => [
                    'title' => 'Ảnh đại diện',
                    'description' => 'Tải lên ảnh đại diện rõ nét để mọi người dễ dàng nhận ra bạn.',
                    'change' => 'Chọn ảnh mới',
                    'remove' => 'Gỡ ảnh vừa chọn',
                    'helper' => 'Hỗ trợ định dạng PNG hoặc JPG, dung lượng tối đa 5MB.',
                ],
                'contact' => [
                    'title' => 'Thông tin liên hệ',
                    'description' => 'Vui lòng đảm bảo thông tin liên hệ luôn chính xác để tiện hỗ trợ.',
                ],
            ],
            'name' => [
                'label' => 'Tên',
                'placeholder' => 'Họ và tên',
            ],
            'email' => [
                'label' => 'Địa chỉ email',
                'placeholder' => 'Địa chỉ email',
            ],
            'country_code' => [
                'label' => 'Mã quốc gia',
                'placeholder' => 'Ví dụ: +84',
            ],
            'phone' => [
                'label' => 'Số điện thoại',
                'placeholder' => 'Số điện thoại của bạn',
            ],
            'unverified_notice' => 'Email của bạn chưa được xác minh.',
            'resend_verification' => 'Bấm vào đây để gửi lại email xác minh.',
            'verification_sent' => 'Đường dẫn xác minh mới đã được gửi đến email của bạn.',
            'submit' => 'Lưu',
            'success' => 'Đã lưu.',
        ],
    ],
    'password' => [
        'breadcrumb' => 'Cài đặt mật khẩu',
        'head_title' => 'Cài đặt mật khẩu',
        'heading' => [
            'title' => 'Đổi mật khẩu',
            'description' => 'Đảm bảo tài khoản của bạn dùng mật khẩu đủ dài và ngẫu nhiên để an toàn hơn',
        ],
        'form' => [
            'current' => [
                'label' => 'Mật khẩu hiện tại',
                'placeholder' => 'Mật khẩu hiện tại',
            ],
            'new' => [
                'label' => 'Mật khẩu mới',
                'placeholder' => 'Mật khẩu mới',
            ],
            'confirmation' => [
                'label' => 'Xác nhận mật khẩu',
                'placeholder' => 'Xác nhận mật khẩu',
            ],
            'submit' => 'Lưu mật khẩu',
            'success' => 'Đã lưu.',
        ],
    ],
    'appearance' => [
        'breadcrumb' => 'Cài đặt giao diện',
        'head_title' => 'Cài đặt giao diện',
        'heading' => [
            'title' => 'Cài đặt giao diện',
            'description' => 'Điều chỉnh thiết lập giao diện cho tài khoản của bạn',
        ],
    ],
];
