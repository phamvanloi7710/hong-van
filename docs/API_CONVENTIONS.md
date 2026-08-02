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
