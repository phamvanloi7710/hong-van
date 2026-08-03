# ARCHITECTURE DECISIONS

Individual ADR files are the source of truth. P02 reviewed `ARCHITECTURE.md`, `DATABASE_BLUEPRINT.md`, `PAGE_BUILDER_CONTRACT.md`, `API_CONVENTIONS.md` and `SECURITY_BASELINE.md`; no conflicting accepted architecture was found.

Delivery order decision: public frontend/template work was postponed until the owner supplied the template. The owner supplied a WordPress-cloned source on 2026-08-03 and explicitly started the final frontend stage at P18; `FrontEndTemplate/` remains read-only.

| ADR | Decision | Status | Date |
|---|---|---|---|
| [ADR-024](adr/ADR-024-versioned-allowlisted-public-theme.md) | Public theme versioned, token allowlist và server CSS compiler dùng chung cho preview/published | Accepted | 2026-08-03 |
| [ADR-023](adr/ADR-023-normalize-wordpress-clone-into-laravel-vite.md) | Chuẩn hóa WordPress clone thành Blade/Vite, loại runtime WordPress và toàn bộ e-commerce | Accepted | 2026-08-03 |
| [ADR-022](adr/ADR-022-gitlab-reproducible-ci.md) | GitLab CI reproducible theo lockfile, có security gate và artifact checksum | Accepted | 2026-08-03 |
| [ADR-021](adr/ADR-021-permission-scoped-dashboard-and-private-reports.md) | Permission-scoped dashboard, assigned-lead visibility, safe notifications and private queued reports | Accepted | 2026-08-03 |
| [ADR-020](adr/ADR-020-consent-gated-approved-analytics.md) | First-party versioned consent with code-allowlisted analytics providers and conditional CSP | Accepted | 2026-08-03 |
| [ADR-019](adr/ADR-019-managed-technical-seo.md) | Published-canonical sitemaps, managed robots, exact internal redirects and real-data structured schemas | Accepted | 2026-08-03 |
| [ADR-018](adr/ADR-018-typed-seo-metadata-resolution.md) | Typed allowlisted SEO metadata with deterministic fallback, robots and media variants | Accepted | 2026-08-03 |
| [ADR-017](adr/ADR-017-mysql-fulltext-public-search.md) | Native MySQL FULLTEXT with application-maintained accent-folded text | Accepted | 2026-08-03 |
| [ADR-016](adr/ADR-016-showcase-media-and-document-policy.md) | Showcase media ownership and certification document visibility | Accepted | 2026-08-03 |
| [ADR-015](adr/ADR-015-post-content-sanitization-and-publishing.md) | Post HTML sanitization, scheduling and slug history | Accepted | 2026-08-03 |
| [ADR-014](adr/ADR-014-unified-lead-intake-workflow.md) | Unified lead intake, immutable submission and workflow history | Accepted | 2026-08-03 |
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
