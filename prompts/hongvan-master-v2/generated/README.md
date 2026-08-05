# GENERATED GAP PROMPTS

T239 tạo prompt tại đây theo mẫu:

```text
G001_short_slug.md
G002_short_slug.md
```

Mỗi prompt chỉ xử lý một finding độc lập, có severity, evidence, scope, tests và dependency. `QUEUE.json` là nguồn chân lý của generated tasks. Sau khi chạy hết generated queue, đặt `audit_recheck_required=true` để T235–T239 chạy lại. Chỉ khi một vòng audit tìm thấy **0 gap** mới được mở T240.
