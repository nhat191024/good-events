<?php
return [
    'app' => 'Hệ thống',
    'app_settings' => 'Các cài đặt liên quan đến hệ thống',

    'banner' => 'Biểu ngữ',
    'banner_settings' => 'Các cài đặt liên quan đến biểu ngữ',

    'partner' => 'Đối tác',
    'partner_settings' => 'Các cài đặt liên quan đến đối tác',

    'saved' => 'Cài đặt đã được lưu thành công!',

    'sections' => [
        'titles' => [
            'general' => 'Cài đặt chung',
            'titles' => 'Tiêu đề các trang',
            'contact' => 'Thông tin liên hệ',
            'socials' => 'Mạng xã hội',
            'zalo' => 'Cài đặt Zalo API',
        ],

        'descriptions' => [
            'general' => 'Cấu hình các cài đặt chung cho ứng dụng',
            'titles' => 'Cấu hình tiêu đề cho các trang danh mục',
            'contact' => 'Thông tin hiển thị ở footer hoặc trang liên hệ',
            'socials' => 'Liên kết đến các trang mạng xã hội',
            'zalo' => 'Cấu hình để gửi tin nhắn qua Zalo API - Không thay đổi nếu bạn không biết mình đang làm gì!!',
        ],
    ],

    'banners' => [
        'partner' => 'Đối tác',
        'mobile_partner' => 'Đối tác (di động)',
        'design' => 'Thiết kế',
        'mobile_design' => 'Thiết kế (di động)',
        'rental' => 'Vật tư cho thuê',
        'mobile_rental' => 'Vật tư cho thuê (di động)',
    ],

    'fields' => [
        'title' => 'Tiêu đề ứng dụng',
        'description' => 'Mô tả ứng dụng',
        'name' => 'Tên ứng dụng',
        'logo' => 'Logo ứng dụng',
        'favicon' => 'Favicon',
        'zalo_token' => 'Mã truy cập Zalo',
        'zalo_refresh_token' => 'Mã làm mới Zalo',
        'zalo_app_secret' => 'App Secret Zalo',
        'zalo_app_id' => 'App ID Zalo',
        'zalo_admin_phone' => 'Số điện thoại Admin nhận tin nhắn khi thử nghiệm',
        'zalo_otp_template_id' => 'ID mẫu tin nhắn OTP trên Zalo',

        'titles' => [
            'partner' => 'Tiêu đề trang đối tác',
            'design' => 'Tiêu đề trang thiết kế',
            'rental' => 'Tiêu đề trang vật tư cho thuê',
        ],

        'banners' => [
            'partner' => 'Biểu ngữ đối tác',
            'mobile_partner' => 'Biểu ngữ đối tác (di động)',
            'design' => 'Biểu ngữ thiết kế',
            'mobile_design' => 'Biểu ngữ thiết kế (di động)',
            'rental' => 'Biểu ngữ vật tư cho thuê',

            'helper_text' => 'Tải lên các hình ảnh biểu ngữ để hiển thị trên trang tương ứng.',
        ],

        'contact_hotline' => 'Số điện thoại',
        'contact_email' => 'Email liên hệ',

        'socials' => [
            'facebook' => 'Link Facebook',
            'facebook_group' => 'Link Group Facebook',
            'zalo' => 'Link Zalo',
            'youtube' => 'Link Youtube',
            'tiktok' => 'Link Tiktok',
        ],

        'minimum_balance' => 'Số dư tối thiểu',
        'fee_percentage' => 'Phần trăm phí',
        'default_balance' => 'Số dư mặc định',
    ],

    'placeholders' => [
        'default_balance' => 'Số dư mặc định cho đối tác mới',
    ],
];
