# DELIVERY PHASES

## Delivery rule

There are 57 sequential checkpoints, P00 through P56. One prompt produces one reviewable checkpoint and stops. A later prompt may start only when the previous checkpoint is `DONE`, or when a prompt explicitly permits a source-dependent `DEFERRED` result.

## Milestones

| Milestone | Prompts | Scope | Exit gate |
|---|---|---|---|
| M0 — Governance and repository baseline | P00–P03 | Repository/tool baseline, external-source inventory, ADR/module/delivery records, repository hygiene and developer baseline | Rules, source gates, architecture records and clean repository conventions are verifiable |
| M1 — Application bootstrap and Admin shell | P04–P07 | Laravel 13 bootstrap, Angular 22 bootstrap, selective Admin template port and Laravel/Admin build integration | Backend and Admin run/build with the approved versions; Admin is served under `/admin/` without running `Template/` directly |
| M2 — Platform and security foundations | P08–P15 | Database/prefix enforcement, Admin API, Sanctum auth, RBAC, theme preferences, company settings, localization, audit/security foundation | Fresh migration/rollback pass, every table is `hongvan_*`, auth/CSRF/permissions and baseline audit tests pass |
| M3 — Media and public presentation foundation | P16–P20 | Media domain, StayHub Media clone, Blade public foundation, public template port and public theme studio | Media and Blade foundations work; source-dependent P17/P19 are complete or explicitly deferred with blockers recorded |
| M4 — Page Builder and public routing | P21–P31 | Schema/registry, block families, Angular editor, Blade iframe preview, version/publish workflows, templates/locks, global regions and public route/error pages | A versioned document validates, previews with the real Blade renderer, publishes/rolls back safely and renders through public routes |
| M5 — Business content and lead workflows | P32–P41 | Products, catalog, crop solutions, services, transportation, warehouses, leads, news, showcase and public discovery | Context ownership, Admin permissions, public rendering, SEO-ready slugs and feature tests pass without adding ERP/WMS/TMS/e-commerce scope |
| M6 — SEO, analytics and experience quality | P42–P46 | Metadata/social sharing, sitemap/schema/redirect, consent/analytics, Admin dashboard/reports/notifications, accessibility/responsive/performance | SEO/security/privacy checks and agreed accessibility/performance budgets pass on representative public/Admin routes |
| M7 — Data, QA and CI | P47–P50 | Safe demo seeders, backend/static architecture QA, Angular E2E/visual QA and build/CI pipelines | Reproducible clean setup plus automated backend, frontend, E2E, static and build checks pass in CI |
| M8 — Operations, security, UAT and handover | P51–P56 | Docker/production deployment, backup/monitoring, security hardening, content migration/UAT, cutover and final handover | Restore test, security acceptance, content sign-off, production smoke test and operational ownership are complete |

The ranges are contiguous and cover every prompt exactly once: P00–P56.

## Source and environment gates

| Gate | Current status | Blocking checkpoint | Required resolution |
|---|---|---|---|
| PHP 8.5.x | Current CLI is 8.4.1 | Before P04 | Select and verify PHP 8.5.x without changing unrelated projects |
| Admin template | `READY` for inventory, incomplete package | Before P06 acceptance | Confirm license, obtain/replace missing `public/` assets and verify Angular 20.1.3-to-22.1.x dependency compatibility |
| Public frontend template | `MISSING` | P19 | Before starting P19, proactively remind the project owner; supply/review licensed source in `FrontEndTemplate/` and rerun its inventory |
| StayHub Media source | `READY_PORTED_P17` | P17 | Source supplied, re-inventoried, mapped and ported; preserve read-only hash evidence |
| Local WAMP domain | `PENDING_WAMP_RECONFIGURATION` | Before local browser acceptance after P04/P07 | Inspect the exact existing `hongvan.local` virtual-host entry, remove only that entry, and recreate it for this project with document root `D:\www\HongVan\BackEnd\public`; verify hosts mapping and HTTP response |

P17 and P19 may be deferred only because their external source is genuinely missing. Both must be resolved before P54 UAT/production unless the project owner records explicit acceptance of a reduced scope.

## Checkpoint exit requirements

Every checkpoint must satisfy the shared Definition of Done in `docs/DEFINITION_OF_DONE.md` and additionally:

1. Update `docs/CODEX_STATE.md` and `docs/TASK_LEDGER.md`.
2. Record commands and exact results, including skipped checks with a concrete reason.
3. Review the diff and prove read-only source was not changed.
4. Commit only scoped work after applicable checks pass; never push automatically.
5. Stop before the next prompt.

## Delivery dependencies

- P08–P15 depend on a runnable Laravel foundation from P04.
- P06–P07 depend on the Angular 22 target from P05 and the P01 Admin inventory.
- P17 depends on P16 plus a valid StayHub Media source.
- P19–P20 depend on P18 plus a valid public frontend source for an exact template port.
- P21–P31 depend on Settings, Media, Blade renderer, RBAC and audit foundations.
- P32–P41 depend on Media, localization, RBAC, audit and the public rendering foundation.
- P42–P46 depend on stable publishable routes/content contracts.
- P47–P50 depend on feature-complete contracts; they do not replace feature-level tests from earlier prompts.
- P51–P56 depend on CI pass and resolution of all production-blocking source gates.
