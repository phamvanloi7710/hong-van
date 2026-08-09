# AGENTS Hierarchy Audit — T003

Snapshot: 2026-08-09, base HEAD `fe46584952fd0c37c56eed52649380a16f1f2d2d`.

## Inventory

Đã tìm thấy 30 file `AGENTS.md` ngoài dependency/build output:

- Root: `AGENTS.md`.
- Admin: `Admin/AGENTS.md`, `Admin/src/app/AGENTS.md`, `Admin/src/app/features/media/AGENTS.md`, `Admin/src/app/features/page-builder/AGENTS.md`.
- Backend: `BackEnd/AGENTS.md`, `BackEnd/app/AGENTS.md`, các rule domain Leads/Media/PageBuilder/Products/Seo, HTTP, database, resources, Blade, Page Builder Blade blocks, routes và tests.
- Governance/tooling: `docs/AGENTS.md`, `prompts/AGENTS.md`, `prompts/hongvan-master-v2/AGENTS.md`, `scripts/AGENTS.md`, `docker/AGENTS.md`.
- Reference sources: `Template/AGENTS.md`, `FrontEndTemplate/AGENTS.md`, `SourceIntegrations/AGENTS.md`, `SourceIntegrations/StayHubMedia/AGENTS.md` và hai file source-owned trong `StayHubMedia/BackEnd`/`FrontEnd`.

## Precedence and scope result

- Root rules apply to the complete repository; descendant files only add stricter, path-specific constraints.
- Admin descendants preserve Angular 22 standalone, strict typing, typed data access, i18n, Annular layout and build/sync gates.
- Backend descendants preserve Laravel architecture, `hongvan_` database naming/comments, authorization, sanitization, SSR and tests.
- Documentation, prompts, scripts and Docker rules narrow their own operational scope without weakening root security or delivery rules.
- Template and integration trees are read-only. Source-owned StayHub descendant rules describe the external system (`/api/v1`, tenant model and original modules) and must not govern Hồng Vân implementation.

## Change made

Root `AGENTS.md` now explicitly states that nested rules carried by reference sources are contextual inventory/port guidance only and cannot override Hồng Vân root rules. No reference-source file was modified.

## Verification contract

- Inventory command excludes dependency/build output but includes ignored read-only sources.
- Every discovered file has a valid ancestor chain ending at root `AGENTS.md`.
- Required primary boundaries (`Admin`, `BackEnd`, `docs`, `prompts`, `scripts`, `docker`, `Template`, `FrontEndTemplate`, `SourceIntegrations`) each have an `AGENTS.md` boundary.
