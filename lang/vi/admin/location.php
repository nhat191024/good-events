<?php
return [
    'singular' => 'Địa điểm',

    'wards' => 'Phường/Xã',

    'cannot_hidden_has_wards' => 'Không thể ẩn địa điểm có phường/xã trực thuộc.',
    'cannot_delete_has_wards' => 'Không thể xóa địa điểm có phường/xã trực thuộc.',

    'manage_wards' => 'Quản lý phường/xã của :name',

    'fields' => [
        'id' => 'ID',
        'name' => 'Tên',
        'code' => 'Mã',
        'codename' => 'Tên mã',
        'short_codename' => 'Tên mã ngắn',
        'type' => 'Loại',
        'phone_code' => 'Mã vùng',
        'parent_id' => 'Thuộc tỉnh/thành',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
        'deleted_at' => 'Xóa lúc',
    ],

    'placeholders' => [
        'type' => 'Chọn loại địa điểm',
    ],

    'filters' => [
        'province' => 'Tỉnh',
        'city' => 'Thành phố trung ương',
        'special_zone' => 'Đặc khu',
        'ward' => 'Phường',
        'commune' => 'Xã',
    ],

    'actions' => [
        'manage_wards' => 'Quản lý phường/xã',
    ],
];
