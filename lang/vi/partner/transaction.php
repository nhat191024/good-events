<?php
return [
    'wallet' => 'Ví tiền',
    'no_transaction' => 'Chưa có giao dịch',
    'no_transaction_description' => 'Bạn chưa có giao dịch nào trong ví.',

    'types' => [
        'deposit' => 'Nạp tiền',
        'withdraw' => 'Rút tiền',
    ],

    'label' => [
        'id' => 'ID',
        'type' => 'Loại giao dịch',
        'amount' => 'Số tiền',
        'reason' => 'Lý do',
        'old_balance' => 'Số dư cũ',
        'balance' => 'Số dư hiện tại',
        'created_at' => 'Thời gian',
    ],

    'button' => [
        'add_funds' => 'Nạp tiền',
        'submit' => 'Xác nhận',
    ],

    'modal' => [
        'add_funds_title' => 'Nạp tiền vào ví',
        'add_funds_description' => 'Nhập số tiền bạn muốn nạp vào ví.',
    ],

    'form' => [
        'amount' => 'Số tiền',
        'amount_placeholder' => 'Nhập số tiền cần nạp (VND)',
    ],

    'notification' => [
        'add_funds_initiated' => 'Yêu cầu nạp tiền',
        'add_funds_amount' => 'Yêu cầu nạp :amount đã được khởi tạo.',

        'add_funds_success' => 'Thanh toán thành công',
        'add_funds_success_message' => 'Thanh toán của bạn đã thành công. Mã giao dịch: :transactionId',

        'add_funds_failed' => 'Thanh toán thất bại',
        'add_funds_failed_message' => 'Thanh toán của bạn không thành công. Vui lòng thử lại.',

        'add_funds_cancelled' => 'Thanh toán bị hủy',
        'add_funds_cancelled_message' => 'Thanh toán của bạn đã bị hủy.'
    ],
];
