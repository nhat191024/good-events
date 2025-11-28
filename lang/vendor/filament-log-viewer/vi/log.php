<?php

declare(strict_types=1);
return [
    'dashboard' => [
        'title' => 'Trình xem nhật ký',
    ],
    'show' => [
        'title' => 'Xem nhật ký :log',
    ],
    'navigation' => [
        'group' => 'Nhật ký',
        'label' => 'Trình xem nhật ký',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Ngày',
            ],
            'level' => [
                'label' => 'Cấp độ',
            ],
            'message' => [
                'label' => 'Thông điệp',
            ],
            'filename' => [
                'label' => 'Tên tệp',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Xem',
            ],
            'download' => [
                'label' => 'Tải xuống nhật ký :log',
                'bulk' => [
                    'label' => 'Tải xuống các nhật ký',
                    'error' => 'Lỗi khi tải xuống các nhật ký',
                ],
            ],
            'delete' => [
                'label' => 'Xóa nhật ký :log',
                'success' => 'Xóa nhật ký thành công',
                'error' => 'Lỗi khi xóa nhật ký',
                'bulk' => [
                    'label' => 'Xóa các nhật ký đã chọn',
                ],
            ],
            'clear' => [
                'label' => 'Xóa nhật ký :log',
                'success' => 'Xóa nhật ký thành công',
                'error' => 'Lỗi khi xóa nhật ký',
                'bulk' => [
                    'success' => 'Xóa các nhật ký thành công',
                    'label' => 'Xóa các nhật ký đã chọn',
                ],
            ],
            'close' => [
                'label' => 'Đóng',
            ],
        ],
        'detail' => [
            'title' => 'Chi tiết',
            'file_path' => 'Đường dẫn tệp',
            'log_entries' => 'Mục nhật ký',
            'size' => 'Kích thước',
            'created_at' => 'Được tạo vào',
            'updated_at' => 'Được cập nhật vào',
        ],
    ],
    'levels' => [
        'all' => 'Tất cả',
        'emergency' => 'Khẩn cấp',
        'alert' => 'Cảnh báo',
        'critical' => 'Quan trọng',
        'error' => 'Lỗi',
        'warning' => 'Cảnh báo',
        'notice' => 'Thông báo',
        'info' => 'Thông tin',
        'debug' => 'Gỡ lỗi',
    ],
];
