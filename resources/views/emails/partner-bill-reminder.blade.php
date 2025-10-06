<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.partner_bill_reminder.title') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .email-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }

        .reminder-alert {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .countdown {
            background-color: #fee2e2;
            border: 2px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .countdown h3 {
            margin: 0 0 10px 0;
            color: #dc2626;
            font-size: 20px;
        }

        .countdown-timer {
            font-size: 24px;
            font-weight: bold;
            color: #dc2626;
        }

        .bill-info {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .bill-info h3 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            min-width: 120px;
        }

        .info-value {
            color: #333;
            text-align: right;
        }

        .highlight {
            color: #dc2626;
            font-weight: 600;
        }

        .checklist {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .checklist h4 {
            margin-top: 0;
            color: #dc2626;
        }

        .checklist ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .checklist li {
            margin-bottom: 8px;
            color: #dc2626;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .footer {
            background-color: #fef2f2;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #fecaca;
        }

        .contact-info {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>
                @if ($recipientType === 'client')
                    ⏰ Nhắc nhở: Sự kiện sắp diễn ra
                @else
                    ⚡ Chuẩn bị: Sự kiện sắp bắt đầu
                @endif
            </h1>
        </div>

        <div class="content">
            <div class="greeting">
                @if ($recipientType === 'client')
                    Xin chào <strong>{{ $partnerBill->client->name }}</strong>,
                @else
                    Xin chào <strong>{{ $partnerBill->partner->name }}</strong>,
                @endif
            </div>

            <div class="reminder-alert">
                @if ($recipientType === 'client')
                    🎪 Sự kiện của bạn sẽ diễn ra trong 24 giờ tới!
                @else
                    🚀 Bạn có dịch vụ cần thực hiện trong 24 giờ tới!
                @endif
            </div>

            <div class="countdown">
                <h3>⏳ Thời gian còn lại</h3>
                @if ($partnerBill->date && $partnerBill->start_time)
                    @php
                        $eventDateTime = \Carbon\Carbon::parse($partnerBill->date->format('Y-m-d') . ' ' . $partnerBill->start_time->format('H:i:s'));
                        $now = \Carbon\Carbon::now();
                        $diff = $eventDateTime->diffForHumans($now);
                    @endphp
                    <div class="countdown-timer">{{ $diff }}</div>
                @else
                    <div class="countdown-timer">Kiểm tra thời gian cụ thể</div>
                @endif
            </div>

            <div class="bill-info">
                <h3>📋 Thông tin sự kiện</h3>

                <div class="info-row">
                    <span class="info-label">Mã đơn:</span>
                    <span class="info-value highlight">#{{ $partnerBill->code }}</span>
                </div>

                @if ($partnerBill->event)
                    <div class="info-row">
                        <span class="info-label">Sự kiện:</span>
                        <span class="info-value">{{ $partnerBill->event->name }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Ngày:</span>
                    <span class="info-value highlight">{{ $partnerBill->date ? $partnerBill->date->format('d/m/Y') : 'Chưa xác định' }}</span>
                </div>

                @if ($partnerBill->start_time)
                    <div class="info-row">
                        <span class="info-label">Giờ bắt đầu:</span>
                        <span class="info-value highlight">{{ $partnerBill->start_time->format('H:i') }}</span>
                    </div>
                @endif

                @if ($partnerBill->end_time)
                    <div class="info-row">
                        <span class="info-label">Giờ kết thúc:</span>
                        <span class="info-value">{{ $partnerBill->end_time->format('H:i') }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Địa chỉ:</span>
                    <span class="info-value">{{ $partnerBill->address }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">{{ $partnerBill->phone }}</span>
                </div>

                @if ($recipientType === 'client' && $partnerBill->partner)
                    <div class="info-row">
                        <span class="info-label">Nhà cung cấp:</span>
                        <span class="info-value">{{ $partnerBill->partner->name }}</span>
                    </div>
                @endif

                @if ($recipientType === 'partner' && $partnerBill->client)
                    <div class="info-row">
                        <span class="info-label">Khách hàng:</span>
                        <span class="info-value">{{ $partnerBill->client->name }}</span>
                    </div>
                @endif
            </div>

            @if ($recipientType === 'client')
                <div class="checklist">
                    <h4>✅ Checklist chuẩn bị cho khách hàng:</h4>
                    <ul>
                        <li>Xác nhận lại thời gian và địa điểm với nhà cung cấp</li>
                        <li>Chuẩn bị đầy đủ những gì cần thiết cho sự kiện</li>
                        <li>Kiểm tra đường đi và phương tiện di chuyển</li>
                        <li>Lưu số điện thoại liên hệ của nhà cung cấp</li>
                        <li>Đến sớm 15-30 phút so với giờ hẹn</li>
                    </ul>
                </div>

                <div class="contact-info">
                    <strong>📞 Thông tin liên hệ nhà cung cấp:</strong><br>
                    Tên: {{ $partnerBill->partner->name ?? 'N/A' }}<br>
                    Email: {{ $partnerBill->partner->email ?? 'N/A' }}<br>
                    Điện thoại: {{ $partnerBill->partner->phone ?? 'N/A' }}
                </div>

                <p>
                    Chúng tôi hy vọng sự kiện của bạn sẽ diễn ra thành công và để lại những kỷ niệm đẹp!
                </p>
            @else
                <div class="checklist">
                    <h4>🎯 Checklist chuẩn bị cho nhà cung cấp:</h4>
                    <ul>
                        <li>Xem lại yêu cầu dịch vụ của khách hàng</li>
                        <li>Chuẩn bị đầy đủ thiết bị và vật liệu cần thiết</li>
                        <li>Xác nhận lại thời gian và địa điểm với khách hàng</li>
                        <li>Kiểm tra đường đi và lên kế hoạch di chuyển</li>
                        <li>Đảm bảo có mặt đúng giờ hoặc sớm hơn 15 phút</li>
                        <li>Chuẩn bị tinh thần để cung cấp dịch vụ tốt nhất</li>
                    </ul>
                </div>

                <div class="contact-info">
                    <strong>📞 Thông tin liên hệ khách hàng:</strong><br>
                    Tên: {{ $partnerBill->client->name ?? 'N/A' }}<br>
                    Email: {{ $partnerBill->client->email ?? 'N/A' }}<br>
                    Điện thoại: {{ $partnerBill->phone }}
                </div>

                <p>
                    Hãy thực hiện dịch vụ một cách chuyên nghiệp và tận tâm để nhận được đánh giá tích cực từ khách hàng!
                </p>
            @endif

            <div class="cta-section">
                <a class="cta-button" href="#">
                    @if ($recipientType === 'client')
                        Xem chi tiết sự kiện
                    @else
                        Quản lý đơn hàng
                    @endif
                </a>
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>Lưu ý:</strong> Nếu có bất kỳ thay đổi hoặc vấn đề gì, vui lòng liên hệ ngay lập tức.<br>
                Đây là email tự động, vui lòng không reply email này.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} SukiEntot - Event Management Platform
            </p>
        </div>
    </div>
</body>

</html>
