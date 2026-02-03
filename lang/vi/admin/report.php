<?php
return [
    'singular' => 'Báo cáo',

    'infolist' => [
        'report_details' => 'Chi tiết báo cáo',
        'relationships' => 'Các bên liên quan',
        'timestamps' => 'Dấu thời gian',
    ],

    'fields' => [
        'id' => 'ID',
        'title' => 'Tiêu đề',
        'content' => 'Nội dung',
        'user' => 'Người báo cáo',
        'reportedUser' => 'Người bị báo cáo',
        'reportedBill' => 'Show bị báo cáo',
        'status' => 'Trạng thái',
        'created_at' => 'Tạo vào',
        'updated_at' => 'Cập nhật vào',
    ],

    'actions' => [
        'change_status' => 'Thay đổi trạng thái',
        'create_chat' => 'Tạo Chat',
        'view_chat' => 'Xem Chat',
    ],

    'helpers' => [
        'create_chat' => 'Tạo một cuộc trò chuyện mới với người dùng đã báo cáo để thảo luận về vấn đề.',
        'view_chat' => 'Xem cuộc trò chuyện đã tạo với người dùng đã báo cáo.',
    ],

    'status' => [
        'pending' => 'Đang chờ xử lý',
        'reviewed' => 'Đã xem xét',
        'resolved' => 'Đã giải quyết',
    ],
];
