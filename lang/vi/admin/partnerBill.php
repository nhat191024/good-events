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
        'cancel_bill' => 'Hủy đơn',
        'cancel_bill_heading' => 'Xác nhận hủy đơn',
        'cancel_bill_description' => 'Bạn có chắc chắn muốn hủy đơn này không? Hành động này không thể hoàn tác.',
    ],

    'notifications' => [
        'bill_cancelled' => 'Hủy thành công.',
        'bill_cancelled_body' => 'Đơn :code đã được hủy.',
    ],
];
