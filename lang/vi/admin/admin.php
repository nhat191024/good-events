<?php
return [
    'admin' => 'Quản trị viên',

    'fields' => [
        'label' => [
            'avatar' => 'Ảnh đại diện',
            'name' => 'Tên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'role' => 'Vai trò',
        ],
        'placeholder' => [
            'name' => 'Nhập tên',
            'email' => 'Nhập email',
            'password' => 'Nhập mật khẩu',
        ],
        'helper' => [
            'email' => 'Địa chỉ email chỉ dùng để đăng nhập vào hệ thống, không cần thiết phải là email thực.',
            'password' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'role' => 'Admin: có toàn quyền quản trị hệ thống. - Quản lý đối tác: quản lý hệ thống cho thuê đối tác. - Quản lý thiết kế: quản lý hệ thông đăng tải thiết kế. - Quản lý cho thuê vật tư: quản lý hệ thống thuê vật tư.',
        ]
    ],
];
