# ADR-020: Consent-gated approved analytics

- Status: Accepted
- Date: 2026-08-03
- Prompt: P44

## Context

Hồng Vân cần cấu hình analytics trong Admin nhưng không được hardcode tracking script, không gửi PII, không tải tài nguyên ngoài khi khách chưa đồng ý và không mở đường cho Page Builder thực thi JavaScript tùy ý.

## Decision

- Analytics mặc định tắt. Provider chỉ nhận `google_analytics_4`, `google_tag_manager` hoặc `plausible`; URL script, CSP host và định dạng tracking identifier nằm trong registry code đã allowlist, không lấy script tùy ý từ database.
- Tracking identifier được mã hóa hoặc tham chiếu biến môi trường, luôn masked trong Admin API và audit log.
- Consent dùng cookie first-party `HttpOnly`, được Laravel mã hóa, có policy version và thời hạn từ 30 đến 365 ngày. Cookie cần thiết luôn bật; analytics/marketing chỉ bật bằng opt-in và có endpoint thu hồi.
- Không tạo `hongvan_consent_records` vì hiện không có yêu cầu pháp lý/nghiệp vụ cần server record. Điều này tránh lưu IP, user-agent hoặc định danh khách không cần thiết.
- CSP dùng nonce theo từng response và chỉ thêm host cố định của provider sau khi cookie consent analytics hợp lệ. Khi analytics tắt hoặc chưa consent, `script-src`, `connect-src` và `img-src` không có host analytics ngoài.
- Event hook chỉ có `lead_submit` với `lead_type` và `product_view` với `product_public_id`, `locale`; không nhận tên, email, điện thoại, nội dung form, IP hoặc user-agent.
- Banner/preferences public cuối cùng và việc gọi event hook trên trang Blade được hoãn đến giai đoạn frontend cuối theo quyết định của chủ dự án; API/renderer/locale contract đã sẵn sàng.

## Consequences

- Admin dùng quyền `settings.view`/`settings.update` hiện có; không bổ sung permission chồng chéo.
- Thêm provider mới phải cập nhật registry code, validation, CSP và test; không thể thêm bằng URL/script trong settings hay Page Builder.
- Thay `policy_version` làm consent cũ hết hiệu lực và banner cuối cùng phải hỏi lại lựa chọn.
