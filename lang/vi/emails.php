<?php

return [
    // ===== COMMON - Các key dùng chung cho tất cả email =====
    'common' => [
        // Greetings
        'dear' => 'Kính gửi',
        'hello' => 'Xin chào',
        'greeting_client' => 'Xin chào :name,',
        'greeting_partner' => 'Xin chào Đối tác :name,',
        'regards' => 'Trân trọng',

        // Company Info
        'team_name' => 'Đội ngũ Sukientot',
        'company_name' => 'Sukientot',
        'support_email' => 'support@sukientot.com',
        'website' => 'https://sukientot.com',
        'copyright' => '© :year Sukientot - Event Management Platform',

        // Order/Bill Fields
        'order_code' => 'Mã đơn hàng',
        'event_name' => 'Sự kiện',
        'event_date' => 'Ngày sự kiện',
        'event_time' => 'Thời gian sự kiện',
        'start_time' => 'Giờ bắt đầu',
        'end_time' => 'Giờ kết thúc',
        'category' => 'Danh mục',
        'location' => 'Địa điểm',
        'address' => 'Địa chỉ',
        'phone' => 'Số điện thoại',
        'note' => 'Ghi chú',
        'status' => 'Trạng thái',
        'total_amount' => 'Tổng tiền',
        'bill_details' => 'Chi tiết đơn hàng',

        // Party Info
        'client_name' => 'Khách hàng',
        'partner_name' => 'Nhà cung cấp',
        'contact_info' => 'Thông tin liên hệ',

        // Status
        'status_pending' => 'Đang chờ xử lý',
        'status_paid' => 'Đã thanh toán',
        'status_confirmed' => 'Đã xác nhận',
        'status_cancelled' => 'Đã hủy',

        // Actions
        'view_details' => 'Xem chi tiết',
        'contact_support' => 'Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ đội ngũ hỗ trợ của chúng tôi.',

        // Footer
        'automated_email' => 'Đây là email tự động, vui lòng không reply email này.',
        'thanks' => 'Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!',
        'unsubscribe' => 'Hủy đăng ký nhận email',
        'privacy_policy' => 'Chính sách bảo mật',
        'terms_of_service' => 'Điều khoản dịch vụ',
    ],

    // ===== PARTNER BILL RECEIVED - Email đơn hàng mới =====
    'partner_bill_received' => [
        'title' => 'Thông báo đơn đặt dịch vụ',
        'subject' => 'Đơn hàng phù hợp - :code',
        'new_order_notification' => 'Đơn Hàng Mới Phù Hợp Với Dịch Vụ Của Bạn',

        // Messages - Client
        'message_client' => 'Cảm ơn bạn đã đặt dịch vụ! Chúng tôi đã nhận được yêu cầu đặt dịch vụ của bạn và đối tác sẽ xem xét trong thời gian sớm nhất.',
        'next_steps_client' => 'Điều gì sẽ xảy ra tiếp theo?',
        'next_steps_list_client' => [
            'Đối tác sẽ xem xét yêu cầu của bạn',
            'Bạn sẽ nhận được email xác nhận khi được chấp thuận',
            'Bạn có thể liên hệ trực tiếp với đối tác nếu có thắc mắc',
        ],

        // Messages - Partner
        'message_partner' => 'Có một đơn hàng mới phù hợp với dịch vụ của bạn. Vui lòng xem chi tiết và chấp nhận đơn hàng nếu bạn có thể thực hiện.',
        'next_steps_partner' => 'Bạn cần làm gì tiếp theo?',
        'next_steps_list_partner' => [
            'Xem xét kỹ thông tin đơn hàng',
            'Liên hệ khách hàng nếu cần làm rõ',
            'Xác nhận hoặc từ chối đơn hàng trong dashboard',
        ],

        // CTA
        'cta_view_order' => 'Xem chi tiết đơn hàng',
        'cta_accept_order' => 'Xem & Chấp Nhận Đơn Hàng',
    ],

    // ===== PARTNER BILL CONFIRMED - Email xác nhận thanh toán =====
    'partner_bill_confirmed' => [
        'title' => 'Đơn đặt dịch vụ đã được xác nhận',
        'subject' => 'Đơn hàng đã xác nhận - :code',
        'success_banner' => '🎉 Đơn hàng đã được xác nhận thành công!',

        // Messages
        'message_client' => 'Đơn hàng của bạn đã được xác nhận và thanh toán thành công!',
        'message_partner' => 'Bạn đã được khách hàng xác nhận đơn đặt dịch vụ!',

        // Payment Info
        'payment_info' => 'Thông tin thanh toán',
        'payment_status' => 'Trạng thái thanh toán',
        'payment_method' => 'Phương thức thanh toán',
        'transaction_id' => 'Mã giao dịch',
        'paid_amount' => 'Số tiền đã thanh toán',

        // Important Notes - Client
        'important_note_client_title' => '📝 Lưu ý quan trọng:',
        'important_note_client_list' => [
            'Vui lòng có mặt đúng giờ tại địa điểm đã đặt',
            'Liên hệ với nhà cung cấp nếu có bất kỳ thay đổi nào',
            'Chuẩn bị sẵn mọi thứ cần thiết cho sự kiện',
        ],
        'thanks_client' => 'Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Chúng tôi hy vọng sự kiện của bạn sẽ diễn ra thành công tốt đẹp!',

        // Important Notes - Partner
        'important_note_partner_title' => '📝 Nhiệm vụ của bạn:',
        'important_note_partner_list' => [
            'Chuẩn bị đầy đủ dịch vụ theo yêu cầu',
            'Liên hệ với khách hàng để xác nhận chi tiết',
            'Có mặt đúng giờ tại địa điểm',
            'Đảm bảo chất lượng dịch vụ tốt nhất',
        ],
        'thanks_partner' => 'Chúc bạn thực hiện dịch vụ thành công và nhận được đánh giá tích cực từ khách hàng!',

        // CTA
        'cta_client' => 'Xem chi tiết đơn hàng',
        'cta_partner' => 'Quản lý đơn hàng',
    ],

    // ===== PARTNER BILL REMINDER - Email nhắc nhở trước sự kiện =====
    'partner_bill_reminder' => [
        'title' => 'Nhắc nhở sự kiện sắp diễn ra',
        'subject' => 'Nhắc nhở sự kiện - :code',

        // Alert Banners
        'alert_client' => '🎪 Sự kiện của bạn sẽ diễn ra trong 24 giờ tới!',
        'alert_partner' => '🚀 Bạn có dịch vụ cần thực hiện trong 24 giờ tới!',

        // Time Remaining
        'time_remaining_title' => '⏳ Thời gian còn lại',
        'time_remaining_text' => 'Kiểm tra thời gian cụ thể',

        // Checklist - Client
        'checklist_client_title' => '✅ Checklist chuẩn bị cho khách hàng:',
        'checklist_client_list' => [
            'Xác nhận lại thời gian và địa điểm với nhà cung cấp',
            'Chuẩn bị đầy đủ những gì cần thiết cho sự kiện',
            'Kiểm tra đường đi và phương tiện di chuyển',
            'Lưu số điện thoại liên hệ của nhà cung cấp',
            'Đến sớm 15-30 phút so với giờ hẹn',
        ],
        'message_client' => 'Chúng tôi hy vọng sự kiện của bạn sẽ diễn ra thành công và để lại những kỷ niệm đẹp!',

        // Checklist - Partner
        'checklist_partner_title' => '🎯 Checklist chuẩn bị cho nhà cung cấp:',
        'checklist_partner_list' => [
            'Xem lại yêu cầu dịch vụ của khách hàng',
            'Chuẩn bị đầy đủ thiết bị và vật liệu cần thiết',
            'Xác nhận lại thời gian và địa điểm với khách hàng',
            'Kiểm tra đường đi và lên kế hoạch di chuyển',
            'Đảm bảo có mặt đúng giờ hoặc sớm hơn 15 phút',
            'Chuẩn bị tinh thần để cung cấp dịch vụ tốt nhất',
        ],
        'message_partner' => 'Hãy thực hiện dịch vụ một cách chuyên nghiệp và tận tâm để nhận được đánh giá tích cực từ khách hàng!',

        // Contact Info
        'contact_info_client_title' => '📞 Thông tin liên hệ nhà cung cấp:',
        'contact_info_partner_title' => '📞 Thông tin liên hệ khách hàng:',
        'contact_name' => 'Tên',
        'contact_email' => 'Email',
        'contact_phone' => 'Điện thoại',

        // CTA
        'cta_client' => 'Xem chi tiết sự kiện',
        'cta_partner' => 'Quản lý đơn hàng',

        // Footer
        'footer_note' => '<strong>Lưu ý:</strong> Nếu có bất kỳ thay đổi hoặc vấn đề gì, vui lòng liên hệ ngay lập tức.',
    ],

    // ===== PARTNER BILL EXPIRED - Email thông báo đơn hết hạn =====
    'partner_bill_expired' => [
        'title' => 'Đơn hàng đã hết hạn',
        'subject' => 'Đơn hàng hết hạn - :code',

        // Alert Banner
        'alert_message' => '⚠️ Đơn hàng của bạn đã hết hạn do không có đối tác nhận!',

        // Messages
        'message' => 'Chúng tôi rất tiếc phải thông báo rằng đơn đặt dịch vụ của bạn đã hết thời hạn chờ và không có đối tác nào nhận đơn.',

        // Status
        'status_expired' => 'Hết hạn',

        // Reason
        'reason_title' => '📝 Lý do đơn hàng hết hạn:',
        'reason_message' => 'Đơn hàng đã vượt quá thời gian chờ đợi mà không có đối tác nào trong hệ thống xác nhận nhận đơn. Điều này có thể do dịch vụ bạn yêu cầu chưa có đối tác phù hợp tại thời điểm này.',

        // Suggestions
        'suggestions_title' => '💡 Bạn có thể thực hiện các bước sau:',
        'suggestions_list' => [
            'Đặt lại đơn hàng với thời gian linh hoạt hơn',
            'Chọn danh mục dịch vụ khác có nhiều đối tác hơn',
            'Liên hệ bộ phận hỗ trợ để được tư vấn',
            'Kiểm tra lại yêu cầu và địa điểm của bạn',
        ],

        // Apology
        'apology_message' => 'Chúng tôi thành thật xin lỗi vì sự bất tiện này và mong bạn sẽ tiếp tục sử dụng dịch vụ của chúng tôi trong tương lai.',

        // CTA
        'cta_new_order' => 'Đặt đơn hàng mới',
        'cta_contact_support' => 'Liên hệ hỗ trợ',
    ],
];
