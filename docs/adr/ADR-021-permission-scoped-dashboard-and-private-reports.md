# ADR-021: Permission-scoped dashboard and private reports

- Status: Accepted
- Date: 2026-08-03
- Prompt: P45

## Context

Admin needs operational aggregates, database notifications and CSV reports without revealing modules or leads outside the current administrator's responsibility. Large exports must not block an HTTP request, and notification deep links must not become an open-redirect channel.

## Decision

1. `dashboard.view` opens the dashboard, while each card and activity group additionally requires the corresponding module permission.
2. A user with `leads.view` sees only leads assigned to that user. Global lead visibility requires the explicit `leads.view_all` permission; `super_admin` receives it through the system permission seed.
3. Dashboard aggregates use a permission/user/date/timezone cache key, a short TTL and a version invalidated by product, post, lead, search-log and audit-log model events.
4. Database notifications are queried only through the authenticated user's notification relation. Deep links are reduced to an allowlisted same-origin `/admin/...` path before being returned.
5. Lead reports are generated synchronously below the configured row threshold. Larger reports are queued. Every report is private, owned by its requester, permission-scoped again during generation, expires, and sanitizes CSV formula prefixes.
6. Follow-up time is stored in UTC on the lead so overdue work is based on explicit workflow data rather than an inferred deadline.

## Consequences

- Roles that should see all leads must be granted both `leads.view` and `leads.view_all`.
- Unassigned leads are visible only to users with global lead visibility until a team model is introduced by a future approved prompt.
- Public page-view metrics remain empty until the final public frontend wires consent-gated events; the dashboard does not fabricate them.
- Expired report URLs return `404`; generated files remain on the private disk for a later retention-cleanup operation.
