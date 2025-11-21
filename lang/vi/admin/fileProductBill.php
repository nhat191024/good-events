<?php
return [
    'singular' => 'Hóa đơn File thiết kế',
    'plural' => 'Hóa đơn File thiết kế',

    'fields' => [
        'id' => 'ID',
        'file_product_id' => 'File thiết kế',
        'client_id' => 'Khách hàng',
        'final_total' => 'Tổng thanh toán',
        'status' => 'Trạng thái',
        'created_at' => 'Ngày tạo',
        'updated_at' => 'Ngày cập nhật',
    ],

    'status' => [
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
    ],

    'payment_method' => [
        'qr_transfer' => 'Thanh toán qua mã QR ngân hàng',
        'cash' => 'Tiền mặt',
        'online' => 'Thanh toán trực tuyến',
    ],

    'description' => [
        'qr_transfer' => 'Thanh toán qua tài khoản ngân hàng của Sukientot.',
        'cash' => 'Thanh toán trực tiếp tại văn phòng hoặc buổi gặp mặt.',
        'online' => 'Thanh toán qua cổng thanh toán điện tử được hỗ trợ.',
    ],
];
