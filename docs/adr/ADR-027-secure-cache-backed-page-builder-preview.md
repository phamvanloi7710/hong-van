# ADR-027 — Secure cache-backed Page Builder preview

- Status: Accepted
- Date: 2026-08-03

## Decision

Page Builder preview dùng session có chủ sở hữu và token bí mật chỉ trả về một lần. Server chỉ lưu hash của token trong bảng session; document đang biên tập được lưu tạm trong cache có TTL, mặc định là Redis, thay vì tạo `page_version` sau mỗi lần gõ. URL iframe là route ký có thời hạn, yêu cầu đúng phiên đăng nhập, đúng chủ sở hữu, token còn hạn và payload cache còn tồn tại.

Iframe gọi chính `PageDocumentRenderer` và public layout/CSS như website thật. Preview luôn có `noindex`, CSP giới hạn `frame-ancestors 'self'`, chặn submit/điều hướng và chỉ thêm overlay chọn block không làm thay đổi markup renderer.

Angular và iframe chỉ trao đổi message có channel/type nằm trong allowlist. Hai phía phải kiểm tra chính xác origin, token, schema version; Angular còn kiểm tra `event.source` là đúng iframe hiện tại. Update document được debounce, session có heartbeat và tự tái tạo khi hết hạn.

## Consequences

- Preview document không phải dữ liệu publish và không được dùng làm nguồn public khi session hết hạn.
- Môi trường production phải cung cấp Redis hoặc cache store phân tán tương đương; file cache chỉ là fallback cục bộ cho WAMP không có Redis.
- Mọi message type mới phải được thêm có chủ đích ở cả hai phía và có test chống message giả mạo.
- Publish/version/rollback vẫn thuộc P28; P27 không biến preview cache thành một page version bền vững.
