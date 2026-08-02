# ARCHITECTURE DECISIONS

Individual ADR files are the source of truth. P02 reviewed `ARCHITECTURE.md`, `DATABASE_BLUEPRINT.md`, `PAGE_BUILDER_CONTRACT.md`, `API_CONVENTIONS.md` and `SECURITY_BASELINE.md`; no conflicting accepted architecture was found.

Delivery order decision: all public frontend/template work is postponed until the final project stage, after the owner supplies a complete frontend template. Admin and backend prompts may continue without modifying `FrontEndTemplate/` or public Blade.

| ADR | Decision | Status | Date |
|---|---|---|---|
| [ADR-001](adr/ADR-001-laravel-blade-public.md) | Laravel Blade for the public website | Accepted | 2026-08-02 |
| [ADR-002](adr/ADR-002-angular-admin.md) | Angular SPA for Admin | Accepted | 2026-08-02 |
| [ADR-003](adr/ADR-003-explicit-table-prefix.md) | Explicit `hongvan_` table prefix | Accepted | 2026-08-02 |
| [ADR-004](adr/ADR-004-blade-iframe-preview.md) | Blade iframe for Page Builder preview | Accepted | 2026-08-02 |
| [ADR-005](adr/ADR-005-no-ecommerce.md) | No e-commerce | Accepted | 2026-08-02 |
| [ADR-006](adr/ADR-006-external-source-read-only.md) | External source is read-only | Accepted | 2026-08-02 |
| [ADR-007](adr/ADR-007-monorepo.md) | Monorepo for the platform | Accepted | 2026-08-02 |
| [ADR-008](adr/ADR-008-sanctum-same-origin.md) | Sanctum same-origin cookie/session for Admin | Accepted | 2026-08-02 |
| [ADR-009](adr/ADR-009-database-comments.md) | Bắt buộc comment cho mọi bảng và cột | Accepted | 2026-08-02 |
| [ADR-010](adr/ADR-010-admin-preferences-and-localization.md) | Preferences theo user và i18n Admin `vi/en/zh` | Accepted | 2026-08-02 |
| [ADR-011](adr/ADR-011-localization-routing-and-timezone.md) | Locale public, fallback, translation tables và UTC | Accepted | 2026-08-03 |
| [ADR-012](adr/ADR-012-audit-and-security-foundation.md) | Audit append-only, redaction tập trung và security headers | Accepted | 2026-08-03 |
| [ADR-013](adr/ADR-013-media-storage-and-lifecycle.md) | Media lưu disk/path, xử lý variant qua queue và chặn xóa theo usage | Accepted | 2026-08-03 |
