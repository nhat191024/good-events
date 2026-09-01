# Partner Category Accessories API

Tài liệu này mô tả contract API phụ kiện danh mục và cách phụ kiện được snapshot vào đơn hàng. Mục tiêu là giúp Codex agent và client app phân biệt đúng thư viện tên phụ kiện, cấu hình phụ kiện theo danh mục và dữ liệu phụ kiện đã lưu trên bill.

## Mô hình dữ liệu

Hệ thống có ba lớp dữ liệu:

1. `accessories`: thư viện tên phụ kiện dùng chung do admin CRUD, ví dụ `Loa`, `Bóng`, `Đèn sân khấu`. Bảng này chỉ lưu tên, không lưu phụ phí.
2. `partner_category_accessories`: cấu hình của một danh mục con. Mỗi dòng lưu `partner_category_id` và snapshot `name`.
3. `partner_bill_accessories`: snapshot phụ kiện khách đã chọn khi đặt đơn. Mỗi dòng lưu `partner_bill_id`, `partner_category_accessory_id` và `name` tại thời điểm đặt.

Không thêm `accessory_id` từ thư viện tên vào `partner_category_accessories`. Form Filament dùng giá trị `name` của thư viện làm option và lưu trực tiếp tên đó vào cấu hình category. Vì vậy việc đổi hoặc xóa tên trong thư viện không làm thay đổi category hoặc bill đã tồn tại.

Chỉ danh mục con được cấu hình phụ kiện. Admin quản lý thư viện tên tại `/admin/accessories` và chọn nhiều tên tại mục “Phụ kiện danh mục” trong form danh mục con.

## Lấy phụ kiện của danh mục

Endpoint public:

```http
GET /api/partner-categories/{categoryId}/accessories
Accept: application/json
```

`categoryId` là ID của `PartnerCategory`, không phải slug. Danh mục không có phụ kiện sẽ trả mảng `data` rỗng.

Response:

```json
{
  "data": [
    {
      "id": 12,
      "name": "Loa"
    },
    {
      "id": 13,
      "name": "Đèn sân khấu"
    }
  ]
}
```

`id` trong response là ID của `partner_category_accessories`. Client phải gửi chính ID này khi tạo booking; không có API ID của thư viện `accessories` trong luồng booking.

## Gửi phụ kiện khi quick booking

Endpoint:

```http
POST /api/quick-booking/save
Authorization: Bearer <token>
Content-Type: application/json
```

Payload quick booking có thêm field tùy chọn:

```json
{
  "category_id": 25,
  "accessory_ids": [12, 13]
}
```

Các field booking khác vẫn giữ nguyên. Quy tắc validation của `accessory_ids`:

- Không bắt buộc; không gửi tương đương không chọn phụ kiện.
- Phải là array.
- Mỗi phần tử phải là integer và không được trùng.
- Mỗi ID phải tồn tại trong `partner_category_accessories` và thuộc đúng `category_id` đang đặt.
- Phụ kiện thuộc category khác trả HTTP `422` tại key `accessory_ids.*`.

Quick booking đọc cấu hình category rồi tạo snapshot tên trong `partner_bill_accessories`. Việc admin đổi hoặc xóa tên sau đó không làm thay đổi đơn đã tạo.

Giao diện web quick booking lấy danh sách theo category, cảnh báo rằng lựa chọn có thể phát sinh thêm phí theo báo giá của đối tác và gửi các ID đã chọn qua `accessory_ids`.

## Phụ kiện trong response đơn hàng

Các response đơn hàng phía khách hàng và đối tác có thêm field `accessories`:

```json
{
  "accessories": [
    {
      "id": 31,
      "accessory_id": 12,
      "name": "Loa"
    }
  ]
}
```

Ý nghĩa ID:

- `id`: ID snapshot trong `partner_bill_accessories`.
- `accessory_id`: ID cấu hình trong `partner_category_accessories` đã được chọn lúc booking. Đây không phải ID của bảng thư viện `accessories`.

Các endpoint đã trả field này gồm:

```http
GET /api/orders
GET /api/orders/history
GET /api/orders/{order}
GET /api/orders/history/{order}

GET /api/partner/bills/realtime
GET /api/partner/bills/history
GET /api/partner/bills/pending
GET /api/partner/bills/confirmed
GET /api/partner/bills/{bill}
```

Response tạo quick booking cũng load `accessories`.

## Giá và khả năng phát sinh phí

Phụ kiện không có mức phụ phí cố định do admin khai báo. Giá đối tác nhập khi nhận đơn được lưu trực tiếp vào `partner_bill_details.total`; backend không cộng thêm giá từ danh sách phụ kiện.

UI booking chỉ thông báo phụ kiện có thể làm thay đổi báo giá của đối tác. Response API phụ kiện không trả field `surcharge`.

## Lưu ý migration

Unique index của `partner_bill_accessories` dùng tên rút gọn `bill_accessory_unique` vì MySQL giới hạn identifier ở 64 ký tự.

Nếu migration `2026_09_01_202953_create_partner_bill_accessories_table` từng thất bại ở tên index dài, MySQL có thể đã để lại bảng dở dang. Chỉ xóa bảng đó trước khi migrate lại:

```sql
DROP TABLE IF EXISTS partner_bill_accessories;
```

Không dùng `php artisan migrate:fresh` trên database có dữ liệu.

## Lưu ý cho Codex agent tiếp theo

- Không dùng ID của bảng `accessories` trong payload quick booking.
- Giữ snapshot `name` ở cả category config và bill. Không dùng cột `surcharge` trong nghiệp vụ; cột cũ được giữ lại để tương thích database hiện tại.
- Khi thêm order endpoint mới, eager-load relationship `accessories` trước khi dùng `PartnerBillResource` để tránh thiếu field hoặc N+1 query.
- Nếu đổi schema response phụ kiện, cập nhật đồng thời bốn API resource: client current, client history, partner list/show và partner realtime.
- Endpoint phụ kiện category chỉ đọc dữ liệu; việc lưu lựa chọn thuộc quick booking.
- Dự án hiện có chỉ dẫn “No test for now”; chưa tạo test tự động cho thay đổi này.
