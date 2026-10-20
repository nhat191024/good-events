# API chat: file và video — context tích hợp Flutter

Tài liệu mô tả contract API hiện tại cho hai loại tin nhắn `file` và `video`, cùng các lưu ý khi tích hợp vào Flutter. Các đường dẫn bên dưới đã bao gồm prefix `/api`.

## 1. Xác thực và quyền truy cập

Các API chat yêu cầu:

```http
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Người dùng phải là participant còn hoạt động của phòng để gửi tin hoặc đọc lịch sử. Thành viên đã rời phòng không có quyền này.

## 2. Gửi file/video

```http
POST /api/chat/threads/{thread}/messages
Content-Type: multipart/form-data; boundary=<do-http-client-tao>
```

`{thread}` là ID số nguyên của phòng, không phải ID đơn hàng.

| Trường multipart | Kiểu | Bắt buộc | Ý nghĩa |
|---|---|---|---|
| `type` | string | Có | `file` hoặc `video` |
| `file` | binary file | Khi `type=file` | Một file, tối đa 20 MB |
| `video` | binary file | Khi `type=video` | Một video, tối đa 50 MB |
| `body` | string | Không | Caption, tối đa 20.000 ký tự |
| `client_message_id` | string UUID | Không, nên gửi | Khóa chống tạo trùng khi retry |

Ví dụ các part gửi file:

```text
type = file
file = <binary của bao-gia.pdf; filename="bao-gia.pdf">
body = Báo giá đính kèm
client_message_id = d3883200-af92-4c4f-aa83-fd63662158ee
```

Ví dụ các part gửi video:

```text
type = video
video = <binary của gioi-thieu.mp4; filename="gioi-thieu.mp4">
client_message_id = 4628c783-537a-412f-b9ad-28ccf4046c0f
```

Quy tắc:

- Mỗi tin chỉ có **một file hoặc một video**. Dùng key đơn `file`/`video`, không dùng `files[]`, `file[]` hoặc `videos[]`.
- Không gửi đồng thời `file`, `video` hoặc `images[]` trong tin `file`/`video`.
- Gửi binary qua multipart, không gửi base64 hoặc URL thay cho file.
- Để HTTP client tự tạo multipart boundary. Phải gửi filename có phần mở rộng đúng định dạng.
- Bỏ `type` sẽ được hiểu là `text`, không tự suy ra loại tin từ tệp upload.

### Định dạng và dung lượng

| Nhóm | Phần mở rộng được nhận |
|---|---|
| File nén | `zip`, `rar`, `7z` |
| Tài liệu | `pdf`, `txt`, `csv`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx` |
| Adobe | `psd`, `psb`, `ai`, `eps`, `indd`, `idml`, `aep`, `prproj`, `xd` |
| Video | `mp4`, `mov`, `m4v`, `webm` |

- File: tối đa **20.971.520 byte** (`20480 KB`, 20 × 1024 × 1024).
- Video: tối đa **52.428.800 byte** (`51200 KB`, 50 × 1024 × 1024).
- Đúng ngưỡng được nhận, vượt ngưỡng bị từ chối.
- Backend kiểm tra cả phần mở rộng và MIME nhận diện từ nội dung; MIME do client khai báo không quyết định kết quả.
- Adobe có đuôi hợp lệ vẫn có thể bị từ chối nếu server không nhận diện được MIME hoặc MIME không khớp. Không tự đổi đuôi/MIME để vượt validation.

### Response thành công — HTTP 200

POST trả `message` trực tiếp trong envelope `{ success, message }`:

```json
{
  "success": true,
  "message": {
    "id": 1208,
    "thread_id": 27,
    "user_id": 42,
    "type": "file",
    "body": null,
    "attachments": [
      {
        "id": 305,
        "name": "bao-gia.pdf",
        "file_name": "8bfe28dc-4df5-43cc-97f2-3b5ab8487bd7.pdf",
        "mime_type": "application/pdf",
        "size": 123456,
        "url": "<absolute-media-url>"
      }
    ],
    "location": null,
    "call": null,
    "price_increase_request": null,
    "preview_text": "[Tệp] bao-gia.pdf",
    "created_at": "2026-10-19T12:00:00+00:00",
    "updated_at": "2026-10-19T12:00:00+00:00",
    "user": {
      "id": 42,
      "name": "Người gửi",
      "avatar": null
    }
  }
}
```

`<absolute-media-url>` là placeholder trong ví dụ; response thật chứa URL đầy đủ để tải file/phát video.

Với video, cấu trúc tương tự, `type=video` và attachment chứa metadata video. `preview_text` ưu tiên caption; không có caption thì file dùng `[Tệp] tên_file`, video dùng `[Video]`.

### Ý nghĩa attachment

| Trường | Kiểu | Lưu ý |
|---|---|---|
| `id` | integer | ID media, khác ID message |
| `name` | string | Tên gốc để hiển thị, có phần mở rộng; có thể được rút gọn |
| `file_name` | string | Tên lưu trữ đã đổi thành UUID; không dùng làm tên hiển thị chính |
| `mime_type` | string hoặc null | MIME backend nhận diện; client nên parse nullable |
| `size` | integer | Dung lượng tính bằng byte |
| `url` | string | URL đầy đủ; dùng nguyên giá trị, không tự ghép đường dẫn storage |

`attachments` luôn là mảng, dù tin `file`/`video` chỉ có một phần tử. Nên xử lý mảng rỗng an toàn khi đọc dữ liệu cũ.

## 3. Lịch sử, danh sách phòng và realtime

### Lịch sử tin nhắn

```http
GET /api/chat/threads/{thread}/messages?page=1
```

Response gồm:

- `messages`: mảng phần tử có dạng `{ sender_id, message, user }`.
- `message`: object tin nhắn như trong POST, nhưng **không có `message.user`**; thông tin người gửi nằm tại `user` cùng cấp với `message`.
- `user`: `{ id, name, avatar }`; `avatar` có thể null.
- `hasMore`: boolean, đúng tên camelCase.
- `thread`: `{ id, can_leave, membership_source }`.

Mỗi trang tối đa 12 tin. `page=1` lấy nhóm mới nhất; tăng `page` để lấy lịch sử cũ hơn. Các tin trong một trang được trả theo thời gian tăng dần. API này đồng thời đánh dấu phòng đã đọc.

Phòng không tồn tại/đã bị xóa trả HTTP 200 với `{ "messages": [], "hasMore": false }`, không có `thread`. Phòng tồn tại nhưng người dùng không phải participant trả 403.

### Danh sách phòng

```http
GET /api/chat?side=client&page=1
GET /api/chat?side=partner&page=1
```

Response vẫn dùng `{ threads, has_more, current_page }`. Trong `threads[].latest_message`, sử dụng `type` và `preview_text` để nhận diện tin mới. `latest_message.attachments` hiện **luôn null**, không dùng danh sách phòng để lấy URL file/video; lấy từ lịch sử hoặc realtime.

### Realtime

- Tên event broadcast: `SendMessage`.
- Private channel: `thread.{threadId}` và `user-messages.{userId}` của người nhận.
- API xác thực channel: `POST /api/broadcasting/auth`, dùng Bearer token; việc đăng ký private channel giữ theo cấu hình realtime hiện có của app.
- Event payload có dạng `{ sender_id, message, user }`, giống một phần tử trong `messages` của API lịch sử. Attachment nằm tại `message.attachments`.
- Broadcast chạy qua queue nên có thể đến sau response POST. Một tin có thể xuất hiện từ POST, realtime trên nhiều channel hoặc tải lại lịch sử; gộp theo `message.id`, không append mù quáng.
- FCM là thông báo có thể được gom và trì hoãn, không phải nguồn payload attachment. Khi nhận thông báo, dùng `thread_id` để tải lại lịch sử theo luồng hiện có.

## 4. Retry và lỗi

- Tạo một UUID `client_message_id` cho mỗi lần gửi logic, giữ nguyên khi retry cùng tin; tin mới phải dùng UUID mới.
- Chống trùng theo bộ `(thread_id, user_id, client_message_id)`. Retry đã thành công trả lại message cũ, không tạo media hoặc thông báo mới; đây không phải API cập nhật caption/thay file.
- Response và realtime **không trả `client_message_id`**. Giữ UUID ở trạng thái gửi local; dùng `message.id` được trả về để đồng bộ với dữ liệu server.
- Khi retry upload, tạo lại multipart/file stream; không tái sử dụng stream đã đọc hết.

| HTTP status | Ý nghĩa và xử lý |
|---|---|
| `200` | Thành công, kể cả retry tìm thấy message cũ |
| `401` | Token thiếu/hết hạn/không hợp lệ |
| `403` | Không có quyền truy cập; POST cũng trả 403 nếu phòng không tồn tại/không còn truy cập được |
| `422` | Validation: thiếu file, sai định dạng/MIME, quá dung lượng, UUID không hợp lệ hoặc gửi trộn loại |
| `413` | Request bị PHP/proxy/web server chặn vì dung lượng; có thể không trả JSON |
| `500` | Lỗi server/upload; nhánh lưu tin lỗi trả `{ "success": false, "message": "Failed to send message." }` |

Ví dụ validation error:

```json
{
  "message": "File không được vượt quá 20MB.",
  "errors": {
    "file": ["File không được vượt quá 20MB."]
  }
}
```

Envelope lỗi validation dùng `{ message, errors }`, không có `success`. `message` có thể là bản tổng hợp khi nhiều lỗi; ưu tiên hiển thị `errors.file`, `errors.video` hoặc lỗi theo field tương ứng. Không giả định mọi response lỗi đều là JSON hoặc có cùng envelope.

## 5. Lưu ý tích hợp

- Backend lưu video gốc, không chuyển mã; không có thumbnail, duration, độ phân giải hoặc trạng thái xử lý video trong attachment. Đuôi hợp lệ không bảo đảm codec phát được trên mọi thiết bị; cần xử lý trường hợp tải được nhưng không phát được.
- `type=video` là video đính kèm, khác tin tổng kết cuộc gọi `type=call`; không dùng trường `call` để đọc thông tin video upload.
- File/video hiện dùng URL storage public như ảnh. Không có endpoint download riêng, signed URL hoặc thời điểm hết hạn trong payload. Quyền participant bảo vệ API chat, không biến URL media thành tài nguyên tải xuống có kiểm tra Bearer token.
- URL không được bảo đảm tồn tại lâu dài. Chat gắn đơn `completed`/`cancelled`/`expired`, không hoạt động 14 ngày và không có report liên quan sẽ được dọn; toàn bộ ảnh/file/video của chat cũng bị xóa vật lý. Lịch dọn là 03:30 giờ Việt Nam mỗi ngày, phụ thuộc scheduler đang chạy.
- Khi URL không còn truy cập được, xử lý như attachment không còn khả dụng; không retry tải vô hạn. Media riêng của đơn hàng không bị dọn cùng chat.
- Lịch sử hiện có thể trả trùng tin giữa các trang; luôn gộp theo `message.id`. Tên cờ phân trang khác nhau: lịch sử là `hasMore`, danh sách phòng là `has_more`.
- Kiểm tra kích thước byte trước upload để báo lỗi sớm; kết quả validation server vẫn là quyết định cuối cùng. Có thể dùng tiến độ upload của HTTP client; API hiện không có chunk upload hoặc resume.
- Cấu hình trong repo đã nâng trần PHP/proxy để nhận video 50 MB. Cấu hình PHP ini local của Herd được kiểm tra lúc triển khai vẫn là 2 MB/file, 8 MB/request; upload lớn cần môi trường phục vụ API được cấu hình phù hợp. Chưa xác minh upload 50 MB qua HTTP trên Herd.
