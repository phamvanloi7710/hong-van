# AGENTS.md — ANGULAR ADMIN

- Angular 22.1.x standalone.
- Template nguồn ở `Template/` chỉ đọc.
- Giữ layout, navigation, style, icon và theme settings của template.
- Strict TypeScript.
- Không dùng `any` tùy tiện.
- Built-in control flow.
- Feature lazy-loaded.
- HTTP nằm trong typed data-access service.
- Components quản lý presentation/orchestration vừa phải.
- Form typed và validation map được với backend.
- Permission phải được kiểm tra cả route, UI và backend.
- Theme user lưu server, local cache chỉ để giảm flash.
- Build production phải sync sang `BackEnd/public/admin/browser`.
- Sau feature: lint, test và build.
- Mọi text hiển thị phải đi qua translation key và có đủ bản dịch `vi`, `en`, `zh` trong cùng thay đổi.
- Không hardcode text người dùng trong component/template; tên riêng và technical identifier được giữ nguyên khi phù hợp.
- Favorite menu port theo Annular: tim mở multi-select, shortcut đã chọn chỉ hiện icon + tooltip và nằm bên phải tim.
