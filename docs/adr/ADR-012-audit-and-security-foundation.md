# ADR-012: Audit append-only, redaction tập trung và security headers

- Status: Accepted
- Date: 2026-08-03

## Bối cảnh

Các thao tác xác thực, phân quyền và cấu hình cần một dấu vết thống nhất nhưng không được làm lộ password, token, cookie, secret, nội dung file, IP hoặc user-agent. Admin cần tra cứu lịch sử theo quyền mà không được sửa dữ liệu. Ứng dụng cũng cần security headers, rate limiter và trusted host/proxy đủ rõ để các domain sau tái sử dụng.

## Quyết định

- `AuditTrail` là cổng ghi audit duy nhất cho domain. `AuditRedactor` xử lý đệ quy các key nhạy cảm trước khi insert hoặc ghi security log.
- `hongvan_audit_logs` lưu actor/subject dạng snapshot, before/after/metadata đã redaction, HMAC hash cho IP/user-agent, request ID và thời điểm UTC.
- Audit log không có foreign key đến entity nguồn để lịch sử vẫn nguyên vẹn khi entity bị xóa. Model từ chối update/delete; API P15 chỉ cung cấp `GET` với permission `audit.view` và filter/sort allowlist.
- CSP mặc định không cho inline script. Angular production tắt inline critical CSS để không sinh event handler `onload` trái với CSP nhưng vẫn giữ minify và output hashing.
- Response luôn có `nosniff`, referrer policy và frame policy. Preview cùng origin có policy riêng; HSTS chỉ bật khi production chạy HTTPS.
- Login, public form, upload và preview session dùng limiter có tên. Trusted hosts/proxies, retention audit và security log đều lấy từ environment.

## Hệ quả

- Domain mới phải ghi audit thông qua service tập trung, không insert trực tiếp và không đưa secret vào metadata.
- Không hỗ trợ chỉnh sửa/xóa audit log từ Admin. Retention về sau phải dùng tác vụ hệ thống có kiểm soát, không mở mutation API.
- Endpoint public/upload/preview ở prompt sau phải gắn limiter đã định nghĩa và bổ sung test cho ngưỡng nghiệp vụ thực tế.
- Preview iframe chỉ được nới đúng route/origin đã thiết kế; không nới CSP/frame policy toàn ứng dụng.
