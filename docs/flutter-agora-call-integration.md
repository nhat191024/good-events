# Handoff cho Codex agent: tích hợp Agora Call vào Flutter

Tài liệu này là contract giữa Laravel API của GoodEvent và Flutter App. Agent Flutter phải đọc code/convention hiện có của app trước khi sửa, đặc biệt là API client, authentication, FCM, Laravel Echo/Reverb, state management và routing.

## Mục tiêu

Tích hợp gọi audio/video theo thread chat với các nguyên tắc:

- Người gọi chọn một hoặc nhiều thành viên để nhận thông báo cuộc gọi đến.
- Danh sách được mời chỉ quyết định ai nhận FCM/ringing.
- Mọi thành viên của thread đều thấy call đang diễn ra và có thể tham gia sau.
- Flutter không tự tạo Agora token, UID hoặc channel.
- Tất cả request dùng Sanctum Bearer token và `Accept: application/json`.
- `call.id` là ULID dạng chuỗi, không parse thành số.

## Package Flutter

Ưu tiên package chính thức:

```yaml
dependencies:
  agora_rtc_engine: ^6.6.3
```

Kiểm tra version tương thích với Flutter/Dart hiện tại trước khi thay dependency. Không nâng dependency ngoài phạm vi nếu app đã dùng một version Agora phù hợp.

Quyền thiết bị tối thiểu:

- Android: Internet, microphone, audio settings và Bluetooth/Bluetooth Connect nếu hỗ trợ tai nghe. Camera chỉ cần cho video call.
- iOS: `NSMicrophoneUsageDescription`; thêm `NSCameraUsageDescription` cho video call.
- Chỉ yêu cầu camera khi `call.type == video`.

## API contract

Base path dưới đây là `/api`. Không hardcode host; dùng base URL/config hiện có của Flutter.

### 1. Tạo call

```http
POST /api/chat/threads/{threadId}/calls
Authorization: Bearer <sanctum-token>
Accept: application/json
Content-Type: application/json
```

```json
{
  "type": "audio",
  "invited_user_ids": [12, 18]
}
```

`type` chỉ nhận `audio` hoặc `video`. `invited_user_ids` phải có ít nhất một ID, không trùng, không chứa caller và tất cả phải thuộc thread.

Thành công: HTTP `201`.

```json
{
  "call": {
    "id": "01K1ABCDEF1234567890ABCDEF",
    "callkit_uuid": "c0a8012e-7f58-4b77-90bb-30ffba271234",
    "thread_id": 123,
    "type": "audio",
    "status": "ringing",
    "initiator": {
      "id": 7,
      "name": "Nguyen Van A",
      "avatar": "https://..."
    },
    "invited_users": [
      {
        "id": 12,
        "name": "Tran Van B",
        "avatar": "https://...",
        "status": "pending"
      }
    ],
    "participants": [
      {
        "id": 7,
        "name": "Nguyen Van A",
        "avatar": "https://...",
        "joined_at": "2026-08-02T16:30:00+07:00"
      }
    ],
    "started_at": "2026-08-02T16:30:00+07:00",
    "ended_at": null,
    "expires_at": "2026-08-02T20:30:00+07:00"
  },
  "credentials": {
    "app_id": "agora-app-id",
    "channel": "call_01K1ABCDEF1234567890ABCDEF",
    "uid": 7,
    "token": "007...",
    "expires_in": 3600,
    "expires_at": "2026-08-02T17:30:00+07:00"
  }
}
```

Sau response này, caller khởi tạo Agora engine và join bằng chính `app_id`, `channel`, `uid`, `token` trong `credentials`.

HTTP `409` nghĩa là thread đang có call khác. Khi gặp trường hợp này, gọi active-call API và hiển thị call hiện tại thay vì retry tạo liên tục.

### 2. Lấy call đang hoạt động

```http
GET /api/chat/threads/{threadId}/calls/active
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Response:

```json
{ "call": null }
```

hoặc:

```json
{ "call": { "id": "01K...", "status": "ringing" } }
```

Gọi endpoint này khi:

- Mở màn hình chat/thread.
- App quay lại foreground.
- Reconnect realtime.
- Nhận FCM nhưng local state chưa có call.
- Nhận HTTP `409` khi tạo call.

Endpoint này không trả Agora credentials. User phải gọi `/join` trước khi kết nối Agora.

### 3. Join call hoặc lấy token mới

```http
POST /api/calls/{callId}/join
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Không gửi body. Mọi thành viên thread đều được phép gọi, kể cả không nằm trong `invited_users`.

Response HTTP `200` có cùng shape `{ call, credentials }` như create-call. Dùng credentials trả về cho `RtcEngine.joinChannel()`.

Endpoint này idempotent theo user/call và hiện cũng là endpoint lấy token mới. Khi Agora gọi `onTokenPrivilegeWillExpire`:

1. Gọi lại `POST /api/calls/{callId}/join`.
2. Lấy `credentials.token` mới.
3. Gọi `rtcEngine.renewToken(newToken)`.
4. Không leave/join lại channel nếu `renewToken()` thành công.

UID dùng trong `joinChannel` phải chính xác bằng `credentials.uid`; sai UID sẽ làm token không hợp lệ.

### 4. Rời call

```http
POST /api/calls/{callId}/leave
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Response:

```json
{ "success": true }
```

Flutter phải gọi API này khi user chủ động rời call, sau đó gọi `leaveChannel()` và release/reset engine theo kiến trúc app. Rời call không kết thúc call của người khác.

### 5. Từ chối lời mời

```http
POST /api/calls/{callId}/decline
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Chỉ user có invite trạng thái `pending` mới gọi được. HTTP `409` nghĩa là không còn lời mời đang chờ. Decline chỉ tắt ringing của user đó, không kết thúc call.

### 6. Kết thúc call cho tất cả

```http
POST /api/calls/{callId}/end
Authorization: Bearer <sanctum-token>
Accept: application/json
```

Chỉ initiator được phép gọi. Response:

```json
{ "success": true }
```

Khi nhận realtime payload có `status == ended`, mọi client trong call phải leave Agora channel và đóng/đổi trạng thái màn hình call.

## Tin nhắn tóm tắt sau cuộc gọi

Khi initiator gọi `POST /api/calls/{callId}/end` thành công, backend tự tạo đúng một chat message `type: call`. Sender của message luôn là initiator; Flutter không được tự gửi message tóm tắt này.

Message xuất hiện trong API lịch sử chat và realtime event `.SendMessage`:

```json
{
  "sender_id": 7,
  "message": {
    "id": 987,
    "thread_id": 123,
    "user_id": 7,
    "type": "call",
    "body": null,
    "attachments": [],
    "location": null,
    "call": {
      "id": "01K1ABCDEF1234567890ABCDEF",
      "duration_seconds": 65,
      "started_at": "2026-08-03T20:00:00+07:00",
      "ended_at": "2026-08-03T20:01:05+07:00"
    },
    "preview_text": "[Cuộc gọi]",
    "created_at": "2026-08-03T20:01:05+07:00"
  },
  "user": {
    "id": 7,
    "name": "Nguyen Van A"
  }
}
```

Quy tắc hiển thị phụ thuộc current user:

```dart
final isOutgoing = message.userId == currentUser.id;
final title = isOutgoing ? 'Cuộc gọi đi' : 'Cuộc gọi đến';
```

Không dựa vào `invited_users` để chọn nhãn. Với initiator là “Cuộc gọi đi”; với tất cả user khác là “Cuộc gọi đến”.

Format thời lượng từ `call.duration_seconds`. Backend tính từ lúc participant đầu tiên ngoài caller tham gia đến lúc call kết thúc; nếu không ai tham gia thì bằng `0`. Ví dụ:

```text
0 giây
1 phút 5 giây
1 giờ 2 phút 3 giây
```

Yêu cầu UI card theo mẫu:

- Icon điện thoại và chỉ báo hướng gọi phù hợp outgoing/incoming.
- Tiêu đề `Cuộc gọi đi` hoặc `Cuộc gọi đến`.
- Dòng thời lượng, không tự tính lại từ thời gian local nếu backend đã gửi `duration_seconds`.
- Divider và action `Gọi lại`.
- `Gọi lại` phải mở flow chọn người mời/tạo call mới bằng `POST /chat/threads/{threadId}/calls`; không join lại call ID cũ vì call đó đã `ended`.
- Message call không có bubble text thông thường và không hiện `body: null`.
- Sidebar/preview dùng `preview_text` hoặc map thành nhãn `[Cuộc gọi]`.
- Parser phải cho phép `call` là null để tương thích message cũ hoặc dữ liệu lỗi.

Thêm `call` vào typed chat-message model:

```dart
class CallMessageSummary {
  final String id;
  final int durationSeconds;
  final DateTime? startedAt;
  final DateTime? endedAt;
}
```

Command `php artisan calls:end` dùng để dọn call test không tạo chat message, nhằm tránh làm bẩn lịch sử chat.

## Call model phía Flutter

Tạo typed models, không truyền `Map<String, dynamic>` xuyên UI. Shape tối thiểu:

```dart
enum CallType { audio, video }
enum CallStatus { ringing, active, ended }
enum CallInviteStatus { pending, accepted, declined }

class AgoraCredentials {
  final String appId;
  final String channel;
  final int uid;
  final String token;
  final int expiresIn;
  final DateTime expiresAt;
}
```

Call model cần có `id`, `callkitUuid`, `threadId`, `type`, `status`, `initiator`, `invitedUsers`, `participants`, `startedAt`, `endedAt`, `expiresAt`.

Parser phải:

- Chấp nhận `avatar`, `ended_at` là null.
- Parse timestamp ISO-8601 có timezone.
- Có fallback an toàn khi backend thêm status mới; không crash toàn app vì enum parse.
- Giữ ID user/thread dạng `int`, call ID dạng `String`.

## Realtime

Backend broadcast trên private channel:

```text
thread.{threadId}
```

Tên event:

```text
.CallUpdated
```

Payload:

```json
{
  "call": {
    "id": "01K...",
    "thread_id": 123,
    "status": "active"
  }
}
```

Tích hợp vào subscription thread hiện có. Không tạo kết nối Echo thứ hai nếu app đã có singleton realtime client.

Khi nhận event:

- `ringing`: hiển thị banner/chip “Đang có cuộc gọi”. Chỉ mở incoming-call full screen nếu current user nằm trong `invited_users` với status `pending` hoặc FCM đã đánh dấu incoming call.
- `active`: cập nhật banner và danh sách participants; người không được mời vẫn thấy nút “Tham gia”.
- `ended`: dừng ringing, đóng call UI nếu đang ở trong call, xóa active call khỏi state.
- Merge/replace state theo `call.id`; không append thành nhiều call trùng nhau.

Sau khi realtime reconnect, luôn gọi active-call API để reconcile vì client có thể đã bỏ lỡ event.

## FCM cuộc gọi đến

Chỉ invited users nhận FCM data:

```json
{
  "type": "incoming_call",
  "call_id": "01K...",
  "thread_id": "123",
  "call_type": "audio",
  "initiator_id": "7",
  "initiator_name": "Nguyen Van A"
}
```

Tất cả FCM data là chuỗi. Agent phải parse `thread_id` và `initiator_id` an toàn.

Khi nhận FCM:

1. Không kết nối Agora ngay.
2. Điều hướng/mở incoming-call UI theo convention hiện có.
3. Có thể gọi active-call API để xác nhận call chưa ended/expired.
4. Accept: gọi `/join`, rồi join Agora bằng credentials.
5. Decline: gọi `/decline`, dừng ringing.

Phải xử lý cả foreground, background và notification tap. Không log Agora token hoặc Sanctum token.

## Đăng ký push token theo thiết bị và iOS VoIP

FCM endpoint cũ vẫn hoạt động để tương thích, nhưng call fullscreen phải dùng device registry mới. Mỗi lần login, app start, FCM refresh hoặc PushKit refresh token, gọi:

```http
POST /api/push/devices
Authorization: Bearer <sanctum-token>
Accept: application/json
Content-Type: application/json
```

iOS:

```json
{
  "device_id": "persistent-installation-uuid",
  "platform": "ios",
  "fcm_token": "firebase-registration-token",
  "voip_token": "pushkit-voip-token-hex"
}
```

Android:

```json
{
  "device_id": "persistent-installation-uuid",
  "platform": "android",
  "fcm_token": "firebase-registration-token"
}
```

Có thể gửi riêng `fcm_token` hoặc `voip_token` ở các lần callback khác nhau; backend giữ token còn lại của cùng `device_id`. `device_id` phải được sinh một lần bằng UUID và lưu bền vững theo installation, không dùng model/tên thiết bị và không đổi sau mỗi app launch.

Khi logout, gọi:

```http
DELETE /api/push/devices/{deviceId}
Authorization: Bearer <sanctum-token>
```

Trên iOS, APNs VoIP payload có dạng:

```json
{
  "aps": { "content-available": 1 },
  "type": "incoming_call",
  "call_id": "01K...",
  "callkit_uuid": "c0a8012e-7f58-4b77-90bb-30ffba271234",
  "thread_id": "123",
  "call_type": "audio",
  "initiator_id": "7",
  "initiator_name": "Nguyen Van A",
  "caller_name": "Nguyen Van A",
  "handle": "7",
  "has_video": false,
  "expires_at": "2026-08-04T02:00:00+07:00"
}
```

`callkit_uuid` là UUID chuẩn dành cho CallKit và khác `call_id` ULID dùng với API. Luôn dùng `callkit_uuid` để report/end cuộc gọi trong CallKit; dùng `call_id` để gọi API `/join`, `/decline`, `/leave`, `/end`.

Yêu cầu iOS:

- Đăng ký `PKPushRegistry` cho `.voIP` và gửi token từ `didUpdate pushCredentials` lên endpoint trên.
- Khi nhận VoIP push, phải report ngay `callkit_uuid` cho CallKit trước khi làm network request dài.
- Accept từ CallKit: dùng `call_id` gọi `/join`, sau đó kết nối Agora.
- Decline/end từ CallKit: gọi endpoint backend tương ứng.
- Không dùng PushKit cho notification không liên quan cuộc gọi.
- VoIP token không phải APNs token lấy từ Firebase Messaging; đây là token riêng từ PushKit.

Android không gửi `voip_token`; backend gửi FCM high priority và Flutter dùng full-screen intent/CallStyle theo implementation Android hiện có.

Backend chỉ gửi APNs VoIP thật khi server có đủ cấu hình:

```env
APNS_TEAM_ID=
APNS_KEY_ID=
APNS_BUNDLE_ID=com.sukientot.app
APNS_PRIVATE_KEY=storage/app/private/AuthKey_APNS.p8
APNS_ENVIRONMENT=development
```

Không commit file `.p8`. Dùng `development` với build sandbox và `production` với bản TestFlight/App Store. Queue worker phải xử lý queue `notifications`; nếu thiếu Apple credentials, backend tự fallback sang FCM khi thiết bị có FCM token.

## Khởi tạo Agora engine

Luồng khuyến nghị:

1. Xin microphone permission.
2. `createAgoraRtcEngine()`.
3. `initialize(RtcEngineContext(appId: credentials.appId))`.
4. Register event handler trước khi join.
5. Đặt client role broadcaster.
6. Với audio: enable audio, không bật camera/preview.
7. Không tích hợp các tính năng liên quan đến video call
8. `joinChannel(token, channelId, uid, options)` bằng đúng credentials backend trả về.
9. Theo dõi `onJoinChannelSuccess`, `onUserJoined`, `onUserOffline`, `onConnectionStateChanged` và `onTokenPrivilegeWillExpire`.

Không lưu App Certificate trong Flutter. App ID được backend trả về và không phải secret.

## State machine phía Flutter

Tối thiểu nên có các state:

```text
idle
creating
ringing
joining
connected
reconnecting
leaving
ended
failed
```

Tách hai loại state:

- Server call state: call metadata, invites, participants, status.
- Local RTC state: engine initialization, permission, connection, mute, camera, speaker.

Không suy luận server call đã ended chỉ vì một remote user offline; đó có thể chỉ là user đó rời hoặc mất mạng.

## Xử lý lỗi HTTP

- `401`: token đăng nhập hết hạn; đi qua auth refresh/logout flow hiện có.
- `403`: user không thuộc thread hoặc không có quyền action; đóng incoming UI và refresh active call.
- `404`: call/thread không tồn tại; xóa local call state.
- `409` khi create: thread đã có call; fetch active call.
- `409` khi decline: invite không còn pending; dừng ringing và refresh.
- `422`: hiển thị validation message, đặc biệt danh sách invitee không hợp lệ.
- `429`: debounce/throttle, không retry dồn dập.
- `5xx`: giữ UI recoverable và cho retry; không tự tạo channel/token local.

## Yêu cầu triển khai cho Codex agent

1. Trước khi code, tìm và tái sử dụng API client, auth interceptor, FCM handler, Echo subscription và state-management convention hiện có.
2. Không hardcode base URL, App ID, UID, channel hoặc token.
3. Tạo call repository/service riêng với sáu endpoint ở trên.
4. Tạo typed models và một call coordinator/controller quản lý API + Agora engine + realtime.
5. Không để widget trực tiếp gọi HTTP hoặc điều khiển nhiều instance Agora engine.
6. Bảo đảm cleanup engine/subscription/timer khi widget dispose, logout hoặc call ended.
7. Chặn double tap create/join/end và làm các action idempotent phía UI.
8. Viết test cho JSON parsing, state transitions và repository error mapping theo framework test hiện có của Flutter app.
9. Không sửa Laravel contract nếu chưa trao đổi lại.

## Checklist nghiệm thu

- Caller chọn invitees và tạo audio call thành công.
- Chỉ invitees nhận ringing FCM.
- Thành viên không được mời thấy tin nhắn App đg có cuộc gọi và join được.
- User ngoài thread không join được.
- UID dùng trong Agora khớp response backend.
- Caller và participant nghe được nhau.
- Audio call không yêu cầu camera.
- Hiện tại không có video call
- Participant leave không kết thúc call chung.
- Chỉ initiator end được call.
- Mọi UI đóng khi nhận `status: ended`.
- Sau khi end bình thường, thread nhận đúng một message `type: call` với thời lượng chính xác.
- Message hiển thị “Cuộc gọi đi” cho initiator và “Cuộc gọi đến” cho user khác.
- Nút “Gọi lại” tạo call mới, không join call đã ended.
- App reconcile đúng sau background/foreground và realtime reconnect.
- Token được renew qua `/join` + `renewToken()` trước khi hết hạn.
- Token/credential không xuất hiện trong log, analytics hoặc crash report.

## Tham chiếu backend

- Routes: `routes/api/chat.php`
- Controller: `app/Http/Controllers/Api/Common/CallController.php`
- Response resource: `app/Http/Resources/Api/CallResource.php`
- Policy: `app/Policies/CallPolicy.php`
- Realtime event: `app/Events/CallUpdated.php`
