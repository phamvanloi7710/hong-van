# GAP BACKLOG

Ghi finding ngoài phạm vi task hiện tại. T235–T239 sẽ triage và sinh generated prompts.

- `T010-DOC-01` — `docs/LOCAL_DEVELOPMENT.md` còn mô tả WAMP/PHP/MySQL cũ. Owner: P51/T229. Điều kiện xử lý: Docker manifests được commit; thay hướng dẫn WAMP bằng shared Docker infrastructure, local proxy và test database riêng.
- `T026-OPS-01` — Asset tĩnh do Nginx phục vụ có MIME đúng nhưng chưa có `X-Content-Type-Options` và `Referrer-Policy`; response qua Laravel đã đủ header. Owner: P51/T229. Điều kiện xử lý: bổ sung header toàn cục trong production Nginx manifest và kiểm tra lại public/Admin asset qua domain.
- `T030-DEV-01` — `Admin/package-lock.json` có 4 advisory moderate và 3 high ở dev tooling (`@hono/node-server`, `hono`, `nanoid`, `playwright`); `npm audit --omit=dev --audit-level=high` không có vulnerability production. Owner: T053 dependency security review. Điều kiện xử lý: nâng lockfile tối thiểu tương thích Angular 22, không dùng `npm audit fix --force` vì có thể phá major Angular CLI.
- `T031-OPS-01` — Docker BuildKit trên Windows không đọc được reparse point `Admin/public/storage`, khiến `docker compose up -d --build` dừng trước bước build source. Owner: P51/T229. Điều kiện xử lý: loại generated storage link khỏi root build context hoặc chuyển sang context không chứa link, sau đó build lại app/nginx/queue/scheduler.
