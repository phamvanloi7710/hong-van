# ADR-022: GitLab CI reproducible theo lockfile

- Status: Accepted
- Date: 2026-08-03

## Bối cảnh

Remote chính của repository là GitLab. P50 yêu cầu CI chạy backend, Admin, security và tạo artifact có checksum từ checkout sạch mà không phụ thuộc source template có license.

## Quyết định

- Dùng `.gitlab-ci.yml` làm pipeline chính.
- Runtime khóa theo PHP 8.5, MySQL 8.4, Redis 7.4 và Node 24.15.
- Composer/npm chỉ cài từ lockfile; cache download theo lockfile, không cache dependency đã bung hoặc build output.
- CI chạy migration trên database `hongvan_ci`, prefix check, formatter/static analysis/test/build/audit.
- E2E là job opt-in bằng `RUN_E2E=true` và dùng production Admin bundle.
- Artifact public/Admin có danh sách SHA-256, không commit vào Git.
- Source tham chiếu read-only không phải dependency của CI và bị kiểm tra tracked-file allowlist.

## Hệ quả

- Pipeline GitLab phản ánh đúng remote đang sử dụng và fail sớm khi vi phạm chất lượng.
- PHP job phải biên dịch extension trong container chính thức nên thời gian khởi động dài hơn image tùy biến; P51 có thể tối ưu bằng image CI riêng sau khi Docker/deployment được thiết kế.
- Thay đổi lockfile làm mất cache đúng chủ đích.
