# API CONVENTIONS

## Namespace

```text
/api/admin/v1
```

Public form endpoints có thể dùng:

```text
/api/public/v1
```

Website Blade ưu tiên gọi service nội bộ thay vì HTTP loopback.

Các route foundation đã có:

```text
GET /api/public/v1/system/ping
GET /api/admin/v1/system/ping
GET|POST /api/admin/v1/identity/users
GET|PUT|DELETE /api/admin/v1/identity/users/{public_id}
POST /api/admin/v1/identity/users/{public_id}/activate|lock|reset-sessions
GET|POST /api/admin/v1/identity/roles
GET|PUT|DELETE /api/admin/v1/identity/roles/{public_id}
GET|POST /api/admin/v1/identity/permissions
GET|PUT|DELETE /api/admin/v1/identity/permissions/{public_id}
GET|PUT|DELETE /api/admin/v1/preferences
GET /api/admin/v1/settings
PUT /api/admin/v1/settings/groups/{group_key}
POST|PUT|DELETE /api/admin/v1/settings/branches[/{public_id}]
PUT /api/admin/v1/settings/business-hours/global
PUT /api/admin/v1/settings/branches/{public_id}/business-hours
POST|PUT|DELETE /api/admin/v1/settings/social-links[/{public_id}]
POST|PUT|DELETE /api/admin/v1/settings/contact-channels[/{public_id}]
```

Public ping chỉ trả trạng thái ứng dụng, không trả thông tin dependency hoặc cấu hình. Admin ping dùng `auth` và Gate `system_health`, được P11 ánh xạ vào permission `system.health`. Identity API dùng Sanctum, permission middleware, Policy và Form Request; filter/sort chỉ nhận key trong allowlist phía server.

Settings API dùng `settings.view`, `settings.update` và `settings.manage_settings`. Secret luôn trả `value: null` kèm `has_value`, không trả ciphertext, giá trị giải mã hoặc tên biến môi trường. Public Blade dùng `CompanySettingsViewModel` và cache nội bộ, không gọi HTTP loopback.

## Response thành công

```json
{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "01...",
    "pagination": null
  },
  "message": null
}
```

## Response validation

```json
{
  "success": false,
  "data": null,
  "meta": {
    "request_id": "01..."
  },
  "message": "Dữ liệu không hợp lệ.",
  "errors": {
    "name": ["Tên là bắt buộc."]
  }
}
```

## Quy tắc

- HTTP status phải đúng ngữ nghĩa.
- Không trả exception trace ở production.
- Pagination có `page`, `per_page`, `total`, `last_page`.
- Filter/sort/search phải allowlist.
- Không truyền tên column DB trực tiếp từ request vào `orderBy`.
- Resource trả ID public khi endpoint public.
- Admin có thể dùng numeric ID nội bộ nếu permission và contract cho phép, nhưng ưu tiên public_id nhất quán.
- Date-time ISO 8601 kèm timezone.
- Enum trả cả code và label khi UI cần.
- Bulk action có giới hạn số item.
- Export nặng chạy queue.
- Upload dùng endpoint media chuyên biệt.
- Mỗi endpoint có policy và feature test.
- Idempotency key cho endpoint dễ gửi lặp như public lead nếu phù hợp.

## Request ID

- Mọi request dưới `/api/*` có ULID request ID.
- Client có thể gửi `X-Request-ID`, nhưng server chỉ giữ giá trị ULID hợp lệ; giá trị khác bị thay bằng ULID mới.
- Response trả cùng ID trong header `X-Request-ID` và `meta.request_id`.
- Middleware thêm `request_id` vào shared log context, không log body, token hoặc credential.
- Rate limiter dùng store riêng `CACHE_LIMITER_STORE`; local mặc định `file`, còn production nhiều instance phải cấu hình shared store phù hợp như Redis.

## Locale

- Locale API hiện hỗ trợ `vi`, `en` và `zh`.
- Thứ tự chọn: locale của user nếu có, `X-Locale`, `Accept-Language`, sau đó `API_DEFAULT_LOCALE`.
- Locale ngoài allowlist không được đặt vào application context.
- Response trả `Content-Language` với locale thực tế.
- Admin SPA lưu locale theo user qua Preferences API và gửi locale đang dùng bằng `X-Locale` cho các request tiếp theo.

## Pagination

Danh sách phân trang dùng `ApiResponse::paginated()` và metadata cố định:

```json
{
  "page": 2,
  "per_page": 20,
  "total": 125,
  "last_page": 7
}
```

Mặc định `per_page=20`, tối đa `100`; Form Request của từng endpoint chịu trách nhiệm enforce giới hạn.

## Filter và sort

- Filter dùng `filter[key]=value`; mỗi `key`, DB column, value type và operator phải được server khai báo bằng `AllowedFilter`.
- Sort dùng `sort=name,-created_at`; dấu `-` biểu thị descending.
- Mỗi sort key phải được ánh xạ bằng `AllowedSort`. Raw DB column, SQL fragment hoặc key không có trong allowlist trả `422`.
- `QueryAllowlist` chỉ sinh `QueryCriteria` từ mapping phía server; client không được điều khiển giá trị truyền vào `orderBy`.

## Error status

| Trường hợp | HTTP status |
| --- | ---: |
| Chưa xác thực | `401` |
| Không có quyền | `403` |
| Không tìm thấy | `404` |
| Xung đột trạng thái | `409` |
| Validation/filter/sort sai | `422` |
| Quá giới hạn request | `429` |
| Lỗi không dự kiến | `500` |

`ApiExceptionRenderer` luôn dùng thông báo an toàn cho API và không trả exception class, file, line hoặc stack trace, kể cả khi local bật debug. `ApiResponse` được controller gọi chủ động; không có middleware bọc toàn bộ response nên file download, stream và binary response không bị đổi contract.
