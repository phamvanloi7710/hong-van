# ADR-023: Chuẩn hóa WordPress clone vào Laravel Blade/Vite

- Date: 2026-08-03
- Status: Accepted

## Context

`FrontEndTemplate/` là bản clone tĩnh của một website WordPress, chứa cấu trúc theme/plugin/upload và cả luồng WooCommerce. Source này chỉ dùng để đối chiếu giao diện; nền tảng Hồng Vân không chạy WordPress và không bán hàng online.

## Decision

Port có chọn lọc giao diện sang Laravel Blade/Vite. Asset được phân loại vào `resources/css/public`, `resources/js/public`, `resources/images/public`, `resources/fonts` và view Blade. Không giữ cấu trúc `wp-*`, plugin, PHP, URL demo, cart, checkout, payment, customer account hoặc wishlist trong source đích.

Public content tiếp tục SSR, lấy dữ liệu qua controller/view data, dùng Media/settings nội bộ và giữ đầy đủ `vi`, `en`, `zh`.

## Consequences

- P19 cần nhiều bước đối chiếu hơn việc copy nguyên template nhưng source production nhỏ, rõ quyền sở hữu và dễ bảo trì.
- Visual fidelity được tái tạo bằng component/token thay vì phụ thuộc WordPress runtime.
- Mọi tính năng có nguồn gốc thương mại điện tử bị loại khỏi phạm vi project.
