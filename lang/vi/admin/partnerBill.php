<?php
return [
    'singular' => 'Hóa đơn đối tác',

    'fields' => [
        'code' => 'Mã',
        'address' => 'Địa chỉ',
        'phone' => 'Số điện thoại',
        'date' => 'Ngày',
        'start_time' => 'Giờ bắt đầu',
        'end_time' => 'Giờ kết thúc',
        'final_total' => 'Tổng cuối cùng',
        'event' => 'Sự kiện',
        'client' => 'Khách hàng',
        'partner' => 'Đối tác',
        'category' => 'Danh mục',
        'status' => 'Trạng thái',
        'arrival_photo' => 'Ảnh khi đến',
        'created_at' => 'Tạo lúc',
        'updated_at' => 'Cập nhật lúc',
    ],

    'actions' => [
        'change_status' => 'Thay đổi trạng thái',
        'change_status_description' => 'Chọn trạng thái mới cho hóa đơn này',
        'update' => 'Cập nhật',
    ],

    'notifications' => [
        'status_changed' => 'Trạng thái đã được thay đổi',
        'status_changed_body' => 'Hóa đơn :code đã được thay đổi từ :old_status sang :new_status',
    ],
];
