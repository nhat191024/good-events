<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.partner_bill_confirmed.title') }}</title>
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            fontSize: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .success-banner {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
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

        .highlight {
            color: #dc2626;
            font-weight: 600;
        }

        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
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
                    ✅ Đơn đặt sụ kiện đã được xác nhận
                @else
                    💰 Đơn đặt sụ kiện đã được thanh toán
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

            <div class="success-message">
                @if ($recipientType === 'client')
                    🎉 <strong>Chúc mừng!</strong> Đơn đặt sụ kiện của bạn đã được xác nhận và thanh toán thành công.
                @else
                    💰 <strong>Thông báo!</strong> Khách hàng đã thanh toán cho đơn đặt dịch vụ của bạn.
                @endif
            </div>

            <div class="bill-info">
                <h3>📋 Thông tin đơn đặt dịch vụ</h3>

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

                @if ($partnerBill->category)
                    <div class="info-row">
                        <span class="info-label">Danh mục:</span>
                        <span class="info-value">{{ $partnerBill->category->name }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Ngày sự kiện:</span>
                    <span class="info-value">{{ $partnerBill->date ? $partnerBill->date->format('d/m/Y') : 'Chưa xác định' }}</span>
                </div>

                @if ($partnerBill->start_time)
                    <div class="info-row">
                        <span class="info-label">Giờ bắt đầu:</span>
                        <span class="info-value">{{ $partnerBill->start_time->format('H:i') }}</span>
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

                @if ($partnerBill->final_total)
                    <div class="info-row">
                        <span class="info-label">Tổng tiền:</span>
                        <span class="info-value highlight">{{ number_format($partnerBill->final_total, 0, ',', '.') }} VNĐ</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <span class="status-badge status-paid">Đã thanh toán</span>
                    </span>
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

                @if ($partnerBill->note)
                    <div class="info-row">
                        <span class="info-label">Ghi chú:</span>
                        <span class="info-value">{{ $partnerBill->note }}</span>
                    </div>
                @endif
            </div>

            @if ($recipientType === 'client')
                <div class="important-note">
                    <strong>📝 Lưu ý quan trọng:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Vui lòng có mặt đúng giờ tại địa điểm đã đặt</li>
                        <li>Liên hệ với nhà cung cấp nếu có bất kỳ thay đổi nào</li>
                        <li>Chuẩn bị sẵn mọi thứ cần thiết cho sự kiện</li>
                    </ul>
                </div>

                <p>
                    Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi.
                    Chúng tôi hy vọng sự kiện của bạn sẽ diễn ra thành công tốt đẹp!
                </p>
            @else
                <div class="important-note">
                    <strong>📝 Nhiệm vụ của bạn:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Chuẩn bị đầy đủ dịch vụ theo yêu cầu</li>
                        <li>Liên hệ với khách hàng để xác nhận chi tiết</li>
                        <li>Có mặt đúng giờ tại địa điểm</li>
                        <li>Đảm bảo chất lượng dịch vụ tốt nhất</li>
                    </ul>
                </div>

                <p>
                    Chúc bạn thực hiện dịch vụ thành công và nhận được đánh giá tích cực từ khách hàng!
                </p>
            @endif

            <div class="cta-section">
                <a class="cta-button" href="#">
                    @if ($recipientType === 'client')
                        Xem chi tiết đơn hàng
                    @else
                        Quản lý đơn hàng
                    @endif
                </a>
            </div>
        </div>

        <div class="footer">
            <p>
                Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!<br>
                Đây là email tự động, vui lòng không reply email này.
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} SukiEntot - Event Management Platform
            </p>
        </div>
    </div>
</body>

</html>
