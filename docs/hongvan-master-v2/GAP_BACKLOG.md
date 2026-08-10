# GAP BACKLOG

Ghi finding ngoài phạm vi task hiện tại. T235–T239 sẽ triage và sinh generated prompts.

- `T010-DOC-01` — `docs/LOCAL_DEVELOPMENT.md` còn mô tả WAMP/PHP/MySQL cũ. Owner: P51/T229. Điều kiện xử lý: Docker manifests được commit; thay hướng dẫn WAMP bằng shared Docker infrastructure, local proxy và test database riêng.
- `T026-OPS-01` — Asset tĩnh do Nginx phục vụ có MIME đúng nhưng chưa có `X-Content-Type-Options` và `Referrer-Policy`; response qua Laravel đã đủ header. Owner: P51/T229. Điều kiện xử lý: bổ sung header toàn cục trong production Nginx manifest và kiểm tra lại public/Admin asset qua domain.
