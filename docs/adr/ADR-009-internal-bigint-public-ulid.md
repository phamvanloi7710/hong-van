# ADR-009 — BIGINT nội bộ và ULID công khai

**Status:** Accepted

**Date:** 2026-08-02

## Context

Khóa số tự tăng phù hợp cho foreign key, join và index nội bộ nhưng không nên xuất hiện trong URL hoặc API công khai vì làm lộ thứ tự bản ghi. Dùng ULID làm primary key cho mọi bảng sẽ làm index và foreign key lớn hơn mức cần thiết.

## Decision

- Entity nghiệp vụ dùng `id` kiểu unsigned `BIGINT` làm primary key nội bộ.
- Entity có thể được tham chiếu bên ngoài có thêm `public_id` kiểu `CHAR(26)`, unique và được sinh bằng ULID khi tạo model.
- Route, API response và liên kết công khai dùng `public_id`; không dùng `id` nội bộ làm định danh public.
- Foreign key nội bộ tiếp tục tham chiếu `id` để giữ index gọn và join hiệu quả.
- Bảng framework dùng định danh theo contract gốc của Laravel khi cần, ví dụ UUID của notification, string ID của session/job batch và token reset theo email.

## Consequences

- Cần unique index cho từng `public_id` và test bảo đảm ULID được sinh trước khi insert.
- Model public phải áp dụng `HasPublicId` hoặc cơ chế tương đương thuộc domain.
- Resource và route binding ở các prompt API sau phải chủ động chọn `public_id`.
- Không được dùng `public_id` làm thay đổi kiểu của foreign key nội bộ nếu chưa có lý do nghiệp vụ rõ ràng.
