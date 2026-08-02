# SECURITY BASELINE

## Authentication

- Sanctum cookie/session cùng origin.
- CSRF bắt buộc.
- Regenerate session sau login.
- Secure, HttpOnly, SameSite cookie.
- Rate limit login/reset/2FA.
- 2FA cho role nhạy cảm nếu triển khai.
- Password policy và breach check tùy cấu hình.
- Invalidate session/token khi tài khoản khóa.

## Authorization

- Deny by default.
- Route middleware + Policy.
- Không chỉ ẩn nút ở Angular.
- Permission có namespace, ví dụ `products.view`, `products.update`, `pages.publish`.
- Bulk action kiểm tra từng resource hoặc scope.

## Input/output

- Form Request.
- Escape Blade mặc định.
- Rich text sanitize.
- Upload validate MIME thực.
- No arbitrary file path.
- No arbitrary Blade view.
- No raw template execution.
- Query sort/filter allowlist.
- CSV formula injection prevention khi export.

## Admin

- CSP mặc định `default-src 'self'`, không cho inline script; build Angular production không sinh inline event handler.
- Frame policy mặc định `DENY`; route preview dùng `SAMEORIGIN` và CSP riêng để chuẩn bị iframe cùng origin.
- Response có `X-Content-Type-Options: nosniff` và `Referrer-Policy`; HSTS chỉ bật ở production qua HTTPS.
- postMessage kiểm tra origin và message schema.
- Không lưu token trong localStorage nếu dùng cookie auth.
- Error interceptor không rò secret.
- Logout xóa state.

## Media

- Chỉ nhận extension/MIME trong allowlist; MIME phải được phát hiện từ nội dung và khớp extension phía client.
- Chặn SVG, executable/script prefix, file rỗng, file vượt giới hạn và ảnh không decode được.
- Storage path do server sinh; database không lưu URL public cố định và client không được gửi filesystem path.
- File chỉ được stream qua endpoint đã xác thực/authorize; response dùng `nosniff` và filename đã chuẩn hóa.
- Variant thumbnail/WebP/AVIF chạy queue. Thất bại được ghi vào operation để retry có kiểm soát, không ghi raw file content vào log.
- Usage registry chặn trash/xóa file đang được domain khác tham chiếu. Xóa vĩnh viễn dọn cả original/variant qua Filesystem abstraction và ghi audit.

## Public forms

- Rate limiter có tên cho login, public form, upload và preview session; endpoint tương lai phải gắn đúng limiter.
- Honeypot.
- Optional Turnstile/reCAPTCHA qua setting.
- Server validation.
- Idempotency/deduplication hợp lý.
- Consent checkbox khi thu dữ liệu cá nhân.
- Privacy notice.

## Logging/audit

- Redact password, token, cookie, authorization header.
- Audit login, failed login, user/role changes, publish, delete, settings, media delete.
- P13 chỉ ghi audit key cấu hình đã đổi; toàn bộ value được redaction. Secret cấu hình lưu dạng mã hóa hoặc `env:VARIABLE`, không trả về Angular/public payload và không đưa vào cache public.
- P15 dùng `AuditTrail` tập trung cho auth, identity và settings; dữ liệu nhạy cảm, nội dung file, IP và user-agent không lưu plain text.
- `hongvan_audit_logs` là append-only: model chặn update/delete và Admin chỉ có API/UI đọc theo quyền `audit.view`.
- Security log dùng daily channel riêng; retention audit/security log cấu hình qua environment.

## Deployment

- `APP_DEBUG=false`.
- Secret từ environment.
- Directory permission tối thiểu.
- Public root trỏ `BackEnd/public`.
- Không expose `.env`, storage private, source map admin production nếu không cần.
- HTTPS.
- Trusted host/proxy lấy từ environment; local/testing giữ compatibility phát triển.
- Security headers được áp dụng toàn ứng dụng; HSTS chỉ xuất hiện ở production HTTPS.
- Dependency audit.
- Backup mã hóa và restore test.
