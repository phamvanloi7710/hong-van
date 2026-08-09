# Architecture and ADR Audit — T006

Snapshot: 2026-08-09, base HEAD `659ff0fdfebe8b8a80a24b1eba37b9d2b2c8a878`.

## Architecture evidence

- Monorepo boundaries match source: Laravel/Blade in `BackEnd/`, Angular in `Admin/`, governance in `docs/`/`prompts/`/`scripts/`, infrastructure in `docker/`, and three read-only reference trees.
- `BackEnd/app/Domain/` contains 21 concrete context directories; `ARCHITECTURE.md` now groups all of them without inventing a cross-domain base repository.
- HEAD public flow is Blade SSR plus `/api/public/v1`; Admin is Angular `/admin` plus Sanctum `/api/admin/v1`; preview is signed `/preview` using the Blade renderer.
- HEAD has no dynamic public slug catch-all, menu/global-region schema or renderer; P30/P31 remain missing and are not described as implemented.

## ADR audit

- All 29 ADR files have an Accepted status and date; missing dates on ADR-014/015 were restored from the decision index chronology.
- Root cause: two historical files used `ADR-009`. Database comments keeps ADR-009; the BIGINT/ULID decision is renumbered ADR-029 and its blueprint reference/index are updated.
- No accepted decisions conflict after the identifier fix. ADR-001/002/004/007/008 match the public/Admin/preview/monorepo/auth boundaries at HEAD.
- ADR-020 intentionally rejects server-side consent records; the stale `hongvan_consent_records` blueprint entry was removed.
- ADR-026 historical wording mentions P27 as future work, but its decision remains compatible with implemented ADR-027. Historical ADR bodies were otherwise preserved.
- No new architecture contract was introduced, so no new decision beyond renumbering the existing duplicate was created.

## Stale documentation corrected

- Page Builder status now reflects P21–P29 implemented and P30–P31 pending.
- Preview is no longer labelled deferred.
- Planned menu/global-region tables are explicitly marked as planned, not implemented evidence.
