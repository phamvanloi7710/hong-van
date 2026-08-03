# ADR-025 — Versioned allowlisted Page Builder forms

- Status: Accepted
- Date: 2026-08-03

## Decision

Page Builder chỉ cung cấp bốn loại form do server đăng ký cố định: liên hệ, yêu cầu báo giá sản phẩm, yêu cầu vận chuyển và yêu cầu kho bãi. Mỗi loại có contract theo phiên bản, danh sách field, validation preset, consent và layout do `FormRegistry` sở hữu. Document Page Builder không được lưu field tùy ý, action URL, PHP, Blade hoặc JavaScript thực thi.

Form public dùng Blade POST cùng origin với CSRF, honeypot, rate limit và idempotency key. Dữ liệu được chuyển vào `LeadIntakeService` hiện hữu để lưu theo mô hình lead thống nhất và phát notification qua queue. Form báo giá trên trang sản phẩm tự gắn `public_id` của sản phẩm bằng context do renderer cấp; context này được ký, có thời hạn và được đối chiếu lại với block ID cùng sản phẩm khi submit.

## Consequences

- Thêm hoặc đổi field bắt buộc phải tạo phiên bản contract mới, cập nhật registry, bản dịch `vi/en/zh`, validation backend và test.
- Admin chỉ đọc metadata an toàn từ registry; không nhận route nội bộ hoặc quyền chọn action tùy ý.
- API intake P38 vẫn tương thích vì các field contract của Page Builder chỉ bắt buộc trên route form Blade.
- Product quote block không render form khi thiếu product context; public product routing ở bước sau phải truyền `PageRenderOptions` với product `public_id`.
