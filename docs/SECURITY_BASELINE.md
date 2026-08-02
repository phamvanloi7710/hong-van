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

- CSP.
- Frame policy cho preview được thiết kế rõ.
- postMessage kiểm tra origin và message schema.
- Không lưu token trong localStorage nếu dùng cookie auth.
- Error interceptor không rò secret.
- Logout xóa state.

## Public forms

- Rate limit.
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
- Không cho sửa audit log từ admin thông thường.
- Retention policy.

## Deployment

- `APP_DEBUG=false`.
- Secret từ environment.
- Directory permission tối thiểu.
- Public root trỏ `BackEnd/public`.
- Không expose `.env`, storage private, source map admin production nếu không cần.
- HTTPS.
- Security headers.
- Dependency audit.
- Backup mã hóa và restore test.
