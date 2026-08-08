# Repository Inventory — Master Pack V2

Snapshot: 2026-08-08, HEAD `6b53a5530d0fdbe82d920bf635fc3e705fed7b91`.

## Source boundaries

| Path | Role | Policy |
| --- | --- | --- |
| `BackEnd/` | Laravel backend, Blade public renderer, API, migrations and runtime assets | Primary implementation |
| `Admin/` | Angular standalone admin source | Primary implementation; production build syncs to `BackEnd/public/admin/browser/` |
| `Template/` | Annular admin reference template | Read-only reference |
| `FrontEndTemplate/` | Public frontend reference template | Read-only reference |
| `SourceIntegrations/` | External integration references, including StayHub Media | Read-only reference |
| `docs/` | Architecture, ADR, operations, inventory and project state | Project documentation |
| `prompts/` | Master Pack V2 task definitions and orchestration state | Task control plane |
| `scripts/` | Validation, build and cross-platform helper scripts | Maintained tooling |

## Bounded contexts

- Public presentation: Blade SSR, localized routes, SEO, theme and Page Builder renderer.
- Admin application: Angular shell, navigation, settings, content and domain management.
- Platform foundation: authentication, RBAC, localization, audit, security and media storage.
- Business domains: products, crop solutions, services, transportation, warehouses, leads, posts and showcase.
- Discovery and delivery: search, SEO, analytics/consent, dashboard, reports, CI and deployment.

## Physical snapshot

Counts include files physically present under each boundary; ignored vendor/build/cache files are not source deliverables.

| Path | Files | Bytes |
| --- | ---: | ---: |
| `Admin/` | 27,534 | 285,484,715 |
| `BackEnd/` | 20,690 | 230,066,389 |
| `Template/` | 271 | 568,065 |
| `FrontEndTemplate/` | 558 | 45,852,960 |
| `SourceIntegrations/` | 14,078 | 189,978,353 |
| `docs/` | 94 | 361,280 |
| `prompts/` | 348 | 2,168,523 |
| `scripts/` | 23 | 56,101 |

## Generated, binary and ignored output

- `Admin/dist/`, `Admin/.angular/`, `Admin/coverage/` are generated and ignored.
- `BackEnd/public/admin/browser/` and `BackEnd/public/build/` are generated build outputs and ignored.
- `BackEnd/vendor/`, `Admin/node_modules/` and backend caches/logs are dependencies or runtime state and are not committed.
- `.env` files, uploads, secrets, logs and temporary bootstrap directories are ignored; `.env.example` is the committed environment template.
- `Template/`, `FrontEndTemplate/` and `SourceIntegrations/` remain read-only; approved aggregate fingerprints are recorded in `.readonly-sources.sha256`.

## Evidence and limits

- Root layout matches `AGENTS.md`, `README.md` and `.env.example`.
- The read-only baseline contains aggregate fingerprints for all three reference boundaries.
- This inventory records structure and boundaries only; it does not alter reference sources or claim license approval for their assets.
