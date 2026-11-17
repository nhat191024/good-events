<?php

return [
    'model_label' => 'Thống kê doanh thu',
    'plural_model_label' => 'Thống kê doanh thu',

    'source' => [
        'deposit' => 'Nạp tiền vào ví',
        'asset' => 'Thanh toán file asset design',
    ],

    'fields' => [
        'invoice_number' => 'Số hóa đơn',
        'source' => 'Nguồn doanh thu',
        'paid_at' => 'Ngày thanh toán',
        'reference_code' => 'Mã giao dịch / đơn hàng',
        'buyer' => 'Người mua',
        'buyer_phone' => 'Số điện thoại',
        'description' => 'Nội dung',
        'subtotal' => 'Tiền trước VAT',
        'vat_rate' => 'Thuế suất VAT',
        'vat_amount' => 'Tiền VAT',
        'total' => 'Tổng thanh toán',
        'payment_method' => 'Phương thức thanh toán',
        'order_code' => 'Mã đơn PayOS',
        'bank_reference' => 'Mã tham chiếu ngân hàng',
        'gateway_time' => 'Thời gian giao dịch (PayOS)',
        'payment_reference' => 'Mã liên kết thanh toán PayOS',
        'created_at' => 'Ngày tạo',
    ],

    'export' => [
        'label' => 'Xuất bảng kê hóa đơn (Excel)',
    ],

    'summary' => [
        'total' => 'Tổng',
    ],
];
