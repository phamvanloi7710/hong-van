# DEFINITION OF DONE

## Applicability

This checklist applies to every prompt. Items that genuinely do not apply must be marked `N/A` with a concrete reason in the prompt report; they are not silently skipped.

## Scope and architecture

- The requested prompt only is completed; no later prompt or unrelated refactor is included.
- Root and nearest `AGENTS.md`, current state and prompt are read before changes.
- Implementation follows accepted ADRs, module ownership and existing public contracts.
- Any architectural conflict is documented as a `Proposed` ADR instead of being changed silently.
- `Template/`, `FrontEndTemplate/` and `SourceIntegrations/` remain read-only.

## Code and domain behavior

- Code is production-complete for the scoped behavior; no pseudo-code, hidden TODO or temporary bypass remains.
- Controllers/components remain focused; domain logic is in the approved service/action/data-access boundary.
- Validation covers normal, boundary and invalid input on server and relevant client forms.
- Loading, empty, error, success and authorization-denied states are handled where a UI is changed.
- Date/time, money, locale and public-ID handling follow project conventions.

## Database and migration, when applicable

- Every table is explicitly named `hongvan_*`; package/framework tables are included.
- Migration has required indexes, foreign keys, unique constraints, timestamps and valid `down()` behavior.
- Model `$table`, relationships, casts, fillable/guarded policy and soft-delete behavior match the schema.
- Fresh migration and rollback-by-batch tests pass on an isolated test database.
- No dangerous SQL is run against a real database without explicit approval.

## Backend/API/auth, when applicable

- Form Request validates input and Policy/Gate/middleware authorizes the operation.
- Business mutation uses an Action/Service and an explicit transaction boundary when needed.
- API response follows `docs/API_CONVENTIONS.md`, correct status codes and `/api/admin/v1` or approved public namespace.
- Sanctum cookie/session, CSRF and same-origin behavior are preserved; no token is introduced into browser storage.
- Feature/unit tests cover success, validation, unauthenticated, forbidden and relevant conflict/not-found cases.
- Rate limiting, idempotency, audit and queue behavior are present where the feature requires them.

## Angular Admin, when applicable

- Standalone component, strict typed model and typed data-access service boundaries are preserved.
- Component does not issue direct HTTP when a feature service/data-access boundary exists.
- Lazy route, route guard, permission metadata and menu visibility agree with the server contract.
- Signals/RxJS ownership and cleanup are explicit; no unexplained `any`, ignored error or leaking subscription is introduced.
- Template system, theme and responsive behavior are preserved and verified on the affected route/state.
- Unit tests and relevant E2E/visual checks pass; lint and production build pass within configured budgets.

## Public Blade, Page Builder and SEO, when applicable

- Public Blade uses internal application/domain services, not HTTP loopback.
- Output is escaped/sanitized, accessible and responsive; forms work with server validation and anti-spam controls.
- Metadata, canonical, status code, structured data and sitemap/redirect implications are handled.
- Page Builder blocks use registry allowlists, schema versioning and fixed Blade views; no arbitrary code/CSS/script execution is possible.
- Preview uses signed, expiring, `noindex` Blade iframe output and validated same-origin messaging.
- Preview/public equivalence and published-version immutability have automated or explicit acceptance evidence.

## Security and privacy

- IDOR, mass assignment, XSS, CSRF, injection, unsafe upload/path, secret exposure and permission bypass have been considered for changed paths.
- Logs/audit redact sensitive values and capture required privileged actions.
- Public data collection includes consent/privacy treatment where required.
- Dependency or security configuration changes are documented and reviewed.

## Quality verification

- Formatter passes for changed source.
- Linter/static analysis passes for changed source.
- Targeted unit/feature tests pass, followed by the appropriate broader suite for shared contracts.
- Angular/public asset changes pass production build; migration changes pass fresh/rollback checks.
- Manual/browser QA covers the exact route, role, viewport and authenticated state affected by UI work.
- Commands and exact results are recorded; a failed or unavailable required check prevents `DONE` unless the prompt explicitly permits a truthful `PARTIAL`/`BLOCKED`/`DEFERRED` result.

## Documentation and delivery state

- API/schema/permission/config and operating instructions are updated in the same prompt when changed.
- No endpoint/table/UI is documented as implemented before it exists.
- `docs/CODEX_STATE.md` stays short and reflects current prompt, last completion, gates, latest checks, blockers and next prompt.
- `docs/TASK_LEDGER.md` marks only the completed checkpoint.
- ADR/`docs/DECISIONS.md` is updated for durable architecture decisions.
- Diff contains only scoped files, has no whitespace errors and no secret/binary/reference-source changes.
- Commit is small and scoped after checks pass; no automatic push.
- Final report includes status, scope, files, database/API/UI changes, commands/results, risks, deferred items and next prompt, then stops.
