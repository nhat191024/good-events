<?php
return [
    'singular' => 'Dịch vụ chờ duyệt',

    'modals' => [
        'approve' => [
            'heading' => 'Duyệt dịch vụ',
            'description' => 'Bạn có chắc chắn muốn duyệt dịch vụ này không? Hành động này không thể hoàn tác.',
            'actions' => [
                'approve' => 'Duyệt',
                'cancel' => 'Hủy',
            ],
        ],

        'reject' => [
            'heading' => 'Từ chối dịch vụ',
            'description' => 'Bạn có chắc chắn muốn từ chối dịch vụ này không? Hành động này không thể hoàn tác.',
            'actions' => [
                'reject' => 'Từ chối',
                'cancel' => 'Hủy',
            ],
        ],
    ],

    'fields' => [
        'category' => 'Danh mục',
        'user' => 'Người dùng',
        'status' => 'Trạng thái',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
        'deleted_at' => 'Ngày xóa',
    ],

    'notifications' => [
        'approved' => 'Dịch vụ đã được duyệt thành công.',
        'rejected' => 'Dịch vụ đã bị từ chối.',
    ],

    'actions' => [
        'view_videos' => 'Xem video',
        'approve' => 'Duyệt',
        'reject' => 'Từ chối',
    ],
];
