<?php
return [
    'singular' => 'Mã giảm giá',

    'fields' => [
        'label' => [
            'code' => 'Mã giảm giá',
            'discount_percent' => 'Phần trăm giảm giá (%)',
            'max_discount_amount' => 'Số tiền giảm tối đa',
            'min_order_amount' => 'Giá trị đơn hàng tối thiểu',
            'usage_limit' => 'Giới hạn lượt sử dụng',
            'is_unlimited' => 'Không giới hạn lượt sử dụng',
            'model_type' => 'Áp dụng cho',
            'starts_at' => 'Ngày bắt đầu',
            'expires_at' => 'Ngày hết hạn',
        ],

        'placeholder' => [
            'code' => 'Nhập mã giảm giá',
            'discount_percent' => 'Nhập phần trăm giảm (0-100)',
            'max_discount_amount' => 'Nhập số tiền giảm tối đa',
            'min_order_amount' => 'Nhập giá trị đơn hàng tối thiểu',
            'usage_limit' => 'Nhập số lần sử dụng tối đa',
            'model_type' => 'Chọn loại áp dụng',
            'starts_at' => 'Chọn ngày bắt đầu',
            'expires_at' => 'Chọn ngày hết hạn',
        ],

        'helper' => [
            'discount_percent' => 'Nhập giá trị từ 0 đến 100',
            'max_discount_amount' => 'Số tiền giảm tối đa cho mỗi đơn hàng (để trống nếu không giới hạn)',
            'min_order_amount' => 'Giá trị đơn hàng tối thiểu để áp dụng mã giảm giá',
            'usage_limit' => 'Số lần mã giảm giá có thể được sử dụng (chỉ khi không chọn vô hạn)',
        ],

        'select' => [
            'model_type' => [
                'partner' => 'Đối tác',
            ],
        ],
    ],
];
