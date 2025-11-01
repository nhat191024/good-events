<?php

return [
    'partner_bill_received' => [
        'title' => 'Thông báo đơn đặt dịch vụ',
        'subject' => 'Đơn hàng đã nhận - :code',
        'greeting_client' => 'Kính chào :name,',
        'greeting_partner' => 'Kính chào Đối tác :name,',
        'message_client' => 'Cảm ơn bạn đã đặt dịch vụ! Chúng tôi đã nhận được yêu cầu đặt dịch vụ của bạn và đối tác sẽ xem xét trong thời gian sớm nhất.',
        'message_partner' => 'Bạn có một yêu cầu đặt dịch vụ mới. Vui lòng xem xét thông tin chi tiết bên dưới và phản hồi khách hàng.',
        'bill_details' => 'Chi tiết đơn hàng',
        'order_code' => 'Mã đơn hàng',
        'event_name' => 'Sự kiện',
        'client_name' => 'Khách hàng',
        'partner_name' => 'Đối tác',
        'category' => 'Danh mục',
        'event_date' => 'Ngày sự kiện',
        'event_time' => 'Thời gian sự kiện',
        'location' => 'Địa điểm',
        'phone' => 'Số điện thoại',
        'total_amount' => 'Tổng tiền',
        'status' => 'Trạng thái',
        'note' => 'Ghi chú',
        'next_steps_client' => 'Điều gì sẽ xảy ra tiếp theo?',
        'next_steps_partner' => 'Bạn cần làm gì tiếp theo?',
        'next_steps_list_client' => [
            'Đối tác sẽ xem xét yêu cầu của bạn',
            'Bạn sẽ nhận được email xác nhận khi được chấp thuận',
            'Bạn có thể liên hệ trực tiếp với đối tác nếu có thắc mắc'
        ],
        'next_steps_list_partner' => [
            'Xem xét kỹ thông tin đơn hàng',
            'Liên hệ khách hàng nếu cần làm rõ',
            'Xác nhận hoặc từ chối đơn hàng trong dashboard'
        ],
        'contact_support' => 'Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ đội ngũ hỗ trợ của chúng tôi.',
        'thanks' => 'Cảm ơn bạn đã chọn nền tảng của chúng tôi!',
        'footer_text' => 'Đây là email tự động. Vui lòng không trả lời trực tiếp tin nhắn này.',
        'status_pending' => 'Đang chờ xem xét',
        'cta_view_order' => 'Xem chi tiết đơn hàng',
    ],

    'partner_bill_confirmed' => [
        'title' => 'Đơn đặt dịch vụ đã được xác nhận',
        'subject' => 'Đơn hàng đã xác nhận - :code',
        'greeting_client' => 'Tin tốt, :name!',
        'greeting_partner' => 'Kính chào Đối tác :name,',
        'message_client' => 'Đơn đặt dịch vụ của bạn đã được xác nhận! Đối tác đã chấp nhận yêu cầu và thanh toán đã được xử lý.',
        'message_partner' => 'Bạn đã xác nhận đơn hàng thành công. Thanh toán đã được xử lý và khách hàng đã được thông báo.',
        'success_banner' => '🎉 Đơn hàng đã được xác nhận thành công!',
        'bill_details' => 'Chi tiết đơn hàng đã xác nhận',
        'payment_info' => 'Thông tin thanh toán',
        'payment_status' => 'Trạng thái thanh toán',
        'payment_method' => 'Phương thức thanh toán',
        'transaction_id' => 'Mã giao dịch',
        'paid_amount' => 'Số tiền đã thanh toán',
        'preparation_client' => 'Chuẩn bị sự kiện',
        'preparation_partner' => 'Cung cấp dịch vụ',
        'preparation_list_client' => [
            'Chuẩn bị địa điểm theo thỏa thuận',
            'Đảm bảo tất cả yêu cầu đã sẵn sàng',
            'Sẵn sàng để đối tác liên hệ',
            'Xem xét các chi tiết cuối cùng trước ngày sự kiện'
        ],
        'preparation_list_partner' => [
            'Chuẩn bị tất cả thiết bị cần thiết',
            'Xác nhận logistics và timeline',
            'Liên hệ khách hàng để phối hợp cuối cùng',
            'Đến địa điểm đúng giờ'
        ],
        'contact_info' => 'Thông tin liên hệ',
        'contact_partner' => 'Liên hệ Đối tác',
        'contact_client' => 'Liên hệ Khách hàng',
        'event_reminder' => 'Bạn sẽ nhận được email nhắc nhở 2 giờ trước sự kiện.',
        'thanks_client' => 'Cảm ơn bạn đã tin tưởng chúng tôi với sự kiện đặc biệt của bạn!',
        'thanks_partner' => 'Cảm ơn bạn đã cung cấp dịch vụ xuất sắc cho khách hàng!',
        'cta_prepare' => 'Bắt đầu chuẩn bị',
        'status_paid' => 'Đã thanh toán & Xác nhận',
    ],

    'partner_bill_reminder' => [
        'title' => 'Nhắc nhở sự kiện sắp diễn ra',
        'subject' => 'Nhắc nhở sự kiện - Bắt đầu trong 2 giờ - :code',
        'greeting_client' => 'Xin chào :name,',
        'greeting_partner' => 'Xin chào Đối tác :name,',
        'urgent_reminder' => '⏰ Sự kiện của bạn sắp bắt đầu!',
        'message_client' => 'Đây là lời nhắc nhở thân thiện rằng sự kiện của bạn sẽ bắt đầu trong khoảng 2 giờ nữa. Vui lòng đảm bảo mọi thứ đã sẵn sàng!',
        'message_partner' => 'Đây là lời nhắc nhở rằng bạn có một dịch vụ sẽ bắt đầu trong khoảng 2 giờ nữa. Vui lòng chuẩn bị cho dịch vụ của bạn.',
        'time_remaining' => 'Thời gian còn lại: Khoảng 2 giờ',
        'event_details' => 'Chi tiết sự kiện',
        'checklist_client' => 'Danh sách kiểm tra cuối cùng cho Khách hàng',
        'checklist_partner' => 'Danh sách kiểm tra cuối cùng cho Đối tác',
        'checklist_items_client' => [
            'Địa điểm đã được chuẩn bị và có thể tiếp cận',
            'Tất cả vật dụng cần thiết đã sẵn sàng',
            'Thông tin liên hệ có sẵn',
            'Thanh toán đã được xác nhận'
        ],
        'checklist_items_partner' => [
            'Tất cả thiết bị đã được đóng gói và sẵn sàng',
            'Phương tiện di chuyển đã được sắp xếp',
            'Thông tin liên hệ khách hàng đã được lưu',
            'Timeline dịch vụ đã được xác nhận'
        ],
        'contact_info' => 'Thông tin liên hệ quan trọng',
        'emergency_contact' => 'Đối với các vấn đề khẩn cấp, vui lòng liên hệ:',
        'final_notes' => 'Ghi chú cuối cùng',
        'good_luck' => 'Chúng tôi chúc bạn có một sự kiện thành công và đáng nhớ!',
        'support_available' => 'Đội ngũ hỗ trợ của chúng tôi luôn sẵn sàng nếu bạn cần hỗ trợ.',
        'cta_get_ready' => 'Chuẩn bị ngay',
    ],

    'common' => [
        'dear' => 'Kính gửi',
        'hello' => 'Xin chào',
        'regards' => 'Trân trọng',
        'team_name' => 'Đội ngũ SukiEntot',
        'company_name' => 'SukiEntot',
        'support_email' => 'support@sukientot.com',
        'website' => 'https://sukientot.com',
        'unsubscribe' => 'Hủy đăng ký nhận email',
        'privacy_policy' => 'Chính sách bảo mật',
        'terms_of_service' => 'Điều khoản dịch vụ',
    ]
];
