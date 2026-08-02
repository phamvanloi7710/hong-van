# OPENAPI STRATEGY

**Status:** Accepted foundation strategy

**Date:** 2026-08-02

## Mục tiêu

OpenAPI mô tả contract thật của `/api/admin/v1` và `/api/public/v1`, hỗ trợ review thay đổi, sinh client type khi phù hợp và kiểm tra breaking change trước release. P09 chỉ chốt chiến lược; chưa sinh toàn bộ specification cho các module chưa tồn tại.

## Nguồn chân lý

- Dùng OpenAPI `3.1.x` dạng YAML, quản lý cùng source code.
- Khi endpoint nghiệp vụ bắt đầu ở P10, specification được đặt dưới `docs/openapi/` và cập nhật trong cùng prompt với route, Form Request, Resource và feature test.
- Shared schemas phải phản ánh đúng envelope tại `docs/API_CONVENTIONS.md`: success, validation error, general error, pagination và request ID.
- Không mô tả endpoint hoặc field chưa tồn tại như đã triển khai.

## Quy trình thay đổi

1. Chốt request/response/status/permission của endpoint.
2. Cập nhật OpenAPI cùng implementation.
3. Feature test xác nhận contract và status code quan trọng.
4. Lint specification và kiểm tra breaking change trong CI khi P50 thiết lập pipeline.
5. Client Angular chỉ được sinh từ specification đã lint và được review; generated output không thay thế data-access boundary của feature.

## Security scheme dự kiến

Admin cùng origin sẽ mô tả Sanctum cookie/session và CSRF theo contract thực tế của P10. Không mô tả bearer token trong browser storage. Public endpoint chỉ khai báo anonymous access khi route thực sự công khai; endpoint nhạy cảm phải ghi rate limit và security requirement tương ứng.

## Công cụ

P09 không thêm package. P50 sẽ chọn phiên bản cố định của OpenAPI linter/diff tool, cấu hình CI fail khi YAML sai hoặc có breaking change chưa được duyệt. Không phụ thuộc annotation rải trong controller làm nguồn chân lý duy nhất.
