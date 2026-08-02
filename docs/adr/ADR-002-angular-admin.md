# ADR-002 — Angular SPA cho Admin

**Status:** Accepted

**Date:** 2026-08-02

## Context

Admin cần nhiều màn hình quản trị, RBAC, Media Manager, Page Builder kéo thả, báo cáo và trạng thái tương tác dài. Template Annular tại `Template/` là nguồn tham chiếu Angular 20.1.3, trong khi target bị khóa ở Angular 22.1.x.

## Decision

Admin là Angular 22.1.x standalone SPA tại `Admin/`, dùng strict TypeScript, typed data-access service, lazy-loaded feature và Signals cho state cục bộ. Template Annular được port chọn lọc vào target Angular 22; không nâng hoặc chạy trực tiếp `Template/` thành production source. Production build được tích hợp vào `BackEnd/public/admin/browser/`.

## Consequences

- Admin có ranh giới source/build rõ với Laravel và public Blade.
- Cần kiểm tra compatibility của từng dependency/template component khi port từ Angular 20.1.3 lên 22.1.x.
- Route guard và permission phía Angular chỉ hỗ trợ UX; Laravel Policy/Gate vẫn là lớp quyết định quyền.
- Mỗi feature Angular phải có loading, empty, error state và chạy lint/test/build phù hợp.
