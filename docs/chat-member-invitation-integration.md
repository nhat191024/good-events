# Handoff cho Codex agent: tích hợp mời thành viên vào chat

Tài liệu này là contract giữa Laravel API của GoodEvent và ứng dụng client. Agent Codex phải đọc convention hiện có của client trước khi sửa, đặc biệt là API client, lưu Sanctum token, model chat, state management, routing và cách hiển thị lỗi validation.

## Mục tiêu

Tích hợp bốn chức năng:

- Tìm người dùng theo số điện thoại.
- Mời người dùng vào một thread chat bằng `user_id` trả về từ chức năng tìm kiếm.
- Người được mời đồng ý tham gia thread.
- Người được mời có thể rời thread; thành viên được hệ thống thêm khi thread được khởi tạo không thể rời.

Lời mời và thành viên chat là hai trạng thái khác nhau:

- Khi lời mời đang `pending`, người được mời chưa phải participant và chưa được đọc/gửi tin nhắn hoặc tham gia call.
- Khi đồng ý, API mới tạo participant và chuyển lời mời sang `accepted`.
- Khi rời, participant bị xóa mềm và lời mời chuyển sang `left`.
- Người đã rời có thể được một participant hiện tại mời lại.

## Quy ước chung

- Base path là `/api`; không hardcode host.
- Tất cả endpoint yêu cầu Sanctum Bearer token.
- Gửi header `Accept: application/json`.
- Request có body JSON phải gửi `Content-Type: application/json`.
- `threadId` và `user_id` là số nguyên.
- Không dùng kết quả tìm kiếm theo tên hoặc email để mời; luôn dùng `user.id` trả về từ API tìm theo số điện thoại.

## API contract

### 1. Tìm người dùng theo số điện thoại

```http
GET /api/chat/users/search?phone=090123
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Quy tắc:

- `phone` bắt buộc, dài từ 3 đến 20 ký tự.
- API tìm gần đúng theo chuỗi nhập vào và trả tối đa 20 kết quả.
- Tài khoản đang đăng nhập không xuất hiện trong kết quả.

Thành công: HTTP `200`.

```json
{
  "users": [
    {
      "id": 42,
      "name": "Nguyen Van B",
      "phone": "0901234567"
    }
  ]
}
```

Không tìm thấy user vẫn trả HTTP `200`:

```json
{
  "users": []
}
```

Validation lỗi: HTTP `422`.

```json
{
  "message": "Vui lòng nhập số điện thoại cần tìm.",
  "errors": {
    "phone": ["Vui lòng nhập số điện thoại cần tìm."]
  }
}
```

### 2. Mời người dùng vào thread

```http
POST /api/chat/threads/{threadId}/invitations
Authorization: Bearer <sanctum-token>
Accept: application/json
Content-Type: application/json
```

```json
{
  "user_id": 42
}
```

Chỉ participant hiện tại của thread mới được gọi endpoint này. Không thể tự mời chính mình, mời user đã bị xóa hoặc mời user đang là participant của thread.

Thành công: HTTP `201` khi tạo mới, HTTP `200` khi gửi lại một lời mời cũ.

```json
{
  "message": "Đã gửi lời mời tham gia đoạn chat.",
  "invitation": {
    "id": 15,
    "thread_id": 120,
    "user_id": 42,
    "invited_by_user_id": 7,
    "status": "pending",
    "accepted_at": null,
    "left_at": null
  }
}
```

User đã là participant: HTTP `422`.

```json
{
  "message": "Người dùng đã là thành viên của đoạn chat."
}
```

Người gọi không phải participant hoặc thread không hợp lệ: HTTP `403`.

Validation `user_id` không hợp lệ: HTTP `422` với object `errors` chuẩn của Laravel.

### 3. Đồng ý tham gia thread

Endpoint này phải được gọi bằng token của chính user có lời mời đang chờ.

```http
POST /api/chat/threads/{threadId}/invitations/accept
Authorization: Bearer <sanctum-token-cua-nguoi-duoc-moi>
Accept: application/json
```

Không cần body.

Thành công: HTTP `200`.

```json
{
  "message": "Bạn đã tham gia đoạn chat.",
  "invitation": {
    "id": 15,
    "thread_id": 120,
    "user_id": 42,
    "invited_by_user_id": 7,
    "status": "accepted",
    "accepted_at": "2026-08-04T10:30:00+07:00",
    "left_at": null
  }
}
```

Không có lời mời `pending` cho user hiện tại: HTTP `404`.

```json
{
  "message": "Không tìm thấy lời mời đang chờ."
}
```

Sau khi thành công:

1. Đóng UI xác nhận lời mời.
2. Làm mới danh sách thread hoặc thêm thread vào local state.
3. Điều hướng tới thread nếu UX hiện tại yêu cầu.
4. Chỉ tải messages/call sau khi request accept thành công.

### 4. Rời thread

```http
DELETE /api/chat/threads/{threadId}/participants/me
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Không cần body.

Thành công: HTTP `200`.

```json
{
  "message": "Bạn đã rời đoạn chat."
}
```

Nếu user là thành viên được hệ thống thêm khi khởi tạo thread, chưa chấp nhận lời mời, đã rời trước đó hoặc không thuộc thread: HTTP `403`.

```json
{
  "message": "Chỉ thành viên được mời mới có thể rời đoạn chat."
}
```

Sau khi thành công, xóa thread khỏi local state, đóng màn hình chat nếu đang mở và điều hướng về danh sách chat.

## Trạng thái membership trong thread payload

Mỗi thread trả về từ `GET /api/chat` có thêm hai field:

```json
{
  "id": 120,
  "can_leave": true,
  "membership_source": "invitation"
}
```

Hai field này cũng có trong object `thread` của `GET /api/chat/threads/{threadId}/messages`:

```json
{
  "messages": [],
  "hasMore": false,
  "thread": {
    "id": 120,
    "can_leave": false,
    "membership_source": "system"
  }
}
```

Quy tắc backend:

| membership_source | can_leave | Ý nghĩa |
| --- | --- | --- |
| `invitation` | `true` | User được mời và đã chấp nhận lời mời |
| `system` | `false` | User được hệ thống thêm khi thread được tạo tự động |

App chỉ hiển thị thao tác rời chat khi `can_leave == true`. Không tự suy luận dựa trên số participant hoặc role. Endpoint leave vẫn kiểm tra quyền ở backend để tránh client giả mạo trạng thái.

## Luồng UI đề xuất

### Người mời

1. Từ màn hình chi tiết chat, mở chức năng “Mời thành viên”.
2. Chỉ bắt đầu tìm khi người dùng nhập ít nhất 3 ký tự số điện thoại.
3. Debounce request tìm kiếm khoảng 300–500 ms và hủy request cũ nếu client hỗ trợ.
4. Hiển thị `name`, `phone` và nút “Mời”.
5. Disable nút trong lúc gửi request để tránh submit lặp.
6. Khi thành công, hiển thị message từ API và đánh dấu user là “Đang chờ”.

### Người được mời

1. Nhận FCM có `type == chat_invitation` và đọc `thread_id` từ data payload.
2. Gọi `showChatInvitationDialog(threadId: int.parse(data['thread_id']))`.
3. Khi user chọn “Đồng ý”, gọi endpoint accept trước khi mở nội dung chat.
4. Nếu nhận `404`, coi lời mời không còn hiệu lực và xóa nó khỏi local state.
5. Sau khi accept thành công, refresh danh sách chat.

### Rời chat

Chỉ hiển thị nút “Rời đoạn chat” khi client biết membership đến từ lời mời. Tuy nhiên backend vẫn là nguồn kiểm tra quyền cuối cùng; client phải xử lý HTTP `403` và không được tự giả định rằng mọi participant đều có thể rời.

## Xử lý lỗi chung

- `401`: token thiếu/hết hạn; dùng flow đăng nhập lại hiện có.
- `403`: user không có quyền thực hiện thao tác.
- `404`: không còn lời mời đang chờ.
- `422`: dữ liệu không hợp lệ hoặc user đã là participant.
- `500`: hiển thị lỗi chung và cho phép thử lại; không tự cập nhật local state như thể thao tác đã thành công.

Ưu tiên hiển thị `errors[field][0]` nếu có, nếu không thì dùng `message`.

## Trạng thái và idempotency

Các giá trị `invitation.status`:

| Status | Ý nghĩa |
| --- | --- |
| `pending` | Đang chờ user đồng ý; chưa phải participant |
| `accepted` | Đã đồng ý và đang là participant |
| `left` | Đã rời thread; có thể được mời lại |

Client không được tự chuyển trạng thái trước khi API thành công. Nếu người dùng bấm “Mời” nhiều lần cho cùng một lời mời đang chờ, backend giữ nguyên một invitation thay vì tạo bản ghi trùng.

## Notification lời mời

Sau khi endpoint invite thành công, backend lưu một database notification và gửi FCM tới tất cả thiết bị đã đăng ký của người được mời. Nếu user chưa dùng API đăng ký nhiều thiết bị, backend fallback sang `users.fcm_token` cũ.

FCM notification data payload:

```json
{
  "type": "chat_invitation",
  "code": "CHAT_INVITATION",
  "invitation_id": "15",
  "thread_id": "120",
  "inviter_id": "7",
  "inviter_name": "Nguyen Van A"
}
```

Mọi giá trị trong FCM data payload đều là chuỗi. Client phải kiểm tra `type == chat_invitation`, parse `thread_id` thành số nguyên rồi gọi:

```dart
showChatInvitationDialog(threadId: int.parse(data['thread_id']!));
```

Database notification cũng xuất hiện trong `GET /api/notifications`. Dữ liệu liên quan nằm trong trường `payload`:

```json
{
  "id": "notification-uuid",
  "title": "Lời mời tham gia đoạn chat",
  "message": "Nguyen Van A đã mời bạn tham gia một đoạn chat.",
  "unread": true,
  "created_at": "2026-08-04T10:25:00+07:00",
  "href": null,
  "type": "chat_invitation",
  "thread_id": 120,
  "invitation_id": 15,
  "inviter": {
    "id": 7,
    "name": "Nguyen Van A"
  },
  "payload": {
    "type": "chat_invitation",
    "code": "CHAT_INVITATION",
    "invitation_id": 15,
    "thread_id": 120,
    "inviter_id": 7,
    "inviter_name": "Nguyen Van A"
  }
}
```

Các field `type`, `thread_id`, `invitation_id` và `inviter` đã được normalize ở top-level để màn hình danh sách notification không cần tự đọc cấu trúc database payload. Trường `payload` vẫn được giữ để tương thích với client cũ.

API notification đọc hợp nhất các morph type `User`, `Customer` và `Partner` có cùng user ID. Vì vậy notification lời mời vẫn xuất hiện khi Sanctum token resolve người đang đăng nhập thành `Partner` hoặc `Customer`. Lời mời mới cũng được lưu bằng đúng notifiable type mà polymorphic auth provider sẽ resolve.

Khi app đang foreground, xử lý data payload ngay trong listener FCM. Khi người dùng bấm notification ở background/terminated, xử lý cùng payload trong callback mở notification. Cả hai nhánh phải đi qua cùng một hàm điều hướng để tránh hiển thị dialog hai lần.

API hiện chưa có endpoint riêng để liệt kê pending invitations. Danh sách notification đã lưu có thể được dùng làm fallback; endpoint accept vẫn là nguồn xác nhận cuối cùng rằng lời mời còn hiệu lực.

## Checklist cho Codex agent

- [ ] Tái sử dụng API client và interceptor Sanctum hiện có.
- [ ] Tạo model response theo convention hiện có; không parse ID thành kiểu chuỗi nếu app đang dùng số nguyên.
- [ ] Thêm search có debounce và trạng thái loading/empty/error.
- [ ] Thêm thao tác invite và chống double-submit.
- [ ] Thêm UI accept dựa trên `thread_id` của lời mời.
- [ ] Xử lý `chat_invitation` ở cả foreground và notification-open callback.
- [ ] Refresh danh sách chat sau khi accept.
- [ ] Thêm thao tác leave và xử lý `403`.
- [ ] Xóa thread khỏi local state sau khi leave thành công.
- [ ] Không cho user pending đọc messages hoặc tham gia call.
- [ ] Không thay đổi dependency ngoài phạm vi nếu chưa được yêu cầu.
