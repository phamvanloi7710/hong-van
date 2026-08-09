# T010 Local Docker Runtime Audit

Audit tại base HEAD `a411d81a6db3e09148bbd75af185a8f33454b5e7` ngày 2026-08-09. Theo chỉ đạo của chủ dự án, Docker là runtime local hợp lệ; WAMP không được dùng làm bằng chứng T010.

## Runtime

| Thành phần | Kết quả |
|---|---|
| Docker Desktop engine | `29.6.2` |
| PHP / Laravel / Composer | `8.5.0` / `13.23.0` / `2.8.10` |
| Node.js / npm trong container | `24.15.0` / `11.12.1` |
| MySQL / Redis | `8.4.6` / `7.4.5` |
| Laravel environment | `local`, debug bật, URL `hongvan.local`, timezone `UTC`, locale `vi` |
| Migration | 28 `Ran`, 0 `Pending` |
| Domain | `hongvan.local` phân giải `127.0.0.1` |
| Reverse proxy | `local-proxy` healthy, bind `127.0.0.1:80` |
| Nginx ứng dụng | root `/var/www/html/public`, FastCGI `hongvan-php:9000` |

Các service `app`, `nginx`, `queue`, `scheduler`, `local-mysql` và `local-redis` đều healthy. `phpMyAdmin` phản hồi tại `127.0.0.1:8081`.

## HTTP và MIME

| URL | HTTP | Content-Type |
|---|---:|---|
| `/health` | 200 | `text/html; charset=utf-8` |
| `/api/v1/system/ping` | 200 | `application/json` |
| `/api/admin/v1/system/ping` | 401 | `application/json` |
| `/api/public/v1/system/ping` | 404 | `application/json` |
| `/` | 200 | `text/html; charset=utf-8` |
| `/admin/` | 200 | `text/html; charset=UTF-8` |
| `/admin/dashboard` | 200 | `text/html; charset=UTF-8` |

Admin JS trả `text/javascript`; Admin CSS và public CSS trả `text/css`; public JS trả `application/javascript`. Tất cả asset mẫu đều HTTP 200.

## Giới hạn bằng chứng

- Admin ping authenticated không được gọi vì không dùng credential thật; phản hồi 401 chứng minh route được bảo vệ và trả JSON đúng contract unauthenticated.
- Docker manifests đang thuộc thay đổi P51 chưa commit của chủ dự án. T010 chỉ kiểm chứng runtime, không stage hoặc tuyên bố P51/production deployment đã hoàn tất.
- `docs/LOCAL_DEVELOPMENT.md` còn hướng dẫn WAMP; cần đồng bộ cùng commit Docker/P51 để tài liệu không tham chiếu manifest chưa tồn tại ở HEAD.
