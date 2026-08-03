# P49 — QA Angular, E2E và visual regression

**Status:** PARTIAL

**Date:** 2026-08-03

**Scope:** Angular Admin hiện có. Public frontend và Page Builder được ghi deferred đúng tình trạng repository; không chạy P50.

## Kết quả

- Angular lint: pass.
- Vitest: **26 files, 51 tests, pass**.
- Production build: pass; initial bundle **123.39 kB raw / 27.04 kB estimated transfer**.
- Sync Laravel: **122 files** vào `BackEnd/public/admin/browser`.
- Playwright: **14 tests, pass** trên Chrome, không update snapshot trong lần chạy nghiệm thu.
- Live runtime: đăng nhập thành công tại `hongvan.local`, dashboard và `/admin/products` tải đúng bundle mới, heading sản phẩm tồn tại đúng một lần, console error bằng 0.

## Playwright và isolation

- `PLAYWRIGHT_BASE_URL` cho phép đổi môi trường; URL bắt buộc dùng HTTP/HTTPS và mặc định là `http://hongvan.local/admin/`.
- Mỗi test dùng browser context riêng. Login session, API mutation và dữ liệu CRUD đều được route/mock trong test; không lưu password/auth state vào repository và không ghi vào database WAMP.
- Auto QA guard thất bại test khi có console error, page error, HTTP response từ 400 hoặc failed network; chỉ loại trừ probe `/auth/me` trả 401 có chủ đích trước login và navigation abort vô hại.
- Production asset smoke xác nhận JS/CSS/font đều nằm dưới `/admin/`; không có request source map trong production workflow.

## Workflow đã kiểm tra

| Workflow | Kết quả |
|---|---|
| Login/logout | Pass; mocked session riêng, CSRF endpoint và redirect về login |
| RBAC | Pass; route thiếu quyền redirect về dashboard và menu bị ẩn |
| Theme per user | Pass; preference `green-dark` tạo class `skin-green-dark` |
| Product CRUD | Pass; tạo, xuất bản, xóa và reload danh sách |
| Media Picker | Pass trong product editor; Media Manager còn có folder/search/select/upload/trash/restore |
| Lead workflow | Pass; chuyển trạng thái và thêm ghi chú qua typed endpoints |
| SEO edit | Pass; payload metadata tiếng Việt được PUT đúng entity/locale |
| Accessibility | Pass; skip link/focus, semantic headings/navigation và không overflow ở 390×844, 768×1024, 1440×900 |
| Console/network/assets | Pass; không console/page/network/HTTP error trong 14 workflows |

## Visual regression

- Snapshot mới `admin-shell-desktop-win32.png` được tạo có chủ đích, sau đó mở và review trực tiếp ở kích thước 1600×1000: header, sidebar, dashboard cards, chart, footer và theme drawer không vỡ/che nội dung.
- Snapshot Media Manager hiện có được mở và review lại: folder tree, toolbar, card selection và detail panel đúng layout đã duyệt.
- Lần chạy nghiệm thu cuối dùng `npx playwright test`, không dùng `--update-snapshots`; cả hai snapshot pass.

## Deferred không che giấu

- `/admin/page-builder` vẫn là placeholder vì P21-P31 chưa triển khai. Không thể tạo E2E preview/publish/rollback hoặc snapshot canvas hợp lệ.
- Public Blade/frontend template chưa được cung cấp và theo quyết định chủ dự án phải làm cuối cùng. Vì vậy public home/product/service desktop/tablet/mobile, accessibility và visual snapshot chưa thể chạy.
- Hai nhóm trên phải quay lại trước UAT/production; P49 không tuyên bố coverage giả.

## Commands và kết quả thực tế

- `npm run lint` → pass.
- `npm test -- --watch=false` → 26 files, 51 tests, pass.
- `npm run build:laravel` → pass; build production và sync 122 files.
- `npx playwright test` → 14 tests, pass.
- `npm audit --omit=optional` → còn 3 moderate và 2 high trong toolchain Angular CLI/Playwright; không chạy auto-fix vì đề xuất hiện tại có thay đổi breaking Angular CLI.

## Database/API/UI

- Không migration, table, column hoặc dữ liệu database.
- Không đổi API contract hay runtime component.
- Chỉ thêm cấu hình/test E2E, guard QA, snapshot được review và tài liệu trạng thái. Bundle Admin đã được build/sync theo policy.

## Next prompt

`P50 — Build/CI`. Báo cáo này dừng trước P50.
