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
];
