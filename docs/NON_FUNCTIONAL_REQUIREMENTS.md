# NON-FUNCTIONAL REQUIREMENTS

## Status

Accepted engineering baseline as of 2026-08-02. Production-specific SLA values may be tightened later through an approved change/ADR; they must not be silently weakened.

## Security

- Admin uses same-origin Sanctum cookie/session and CSRF; no access token in browser storage.
- Authorization is deny-by-default and enforced by Laravel middleware plus Policy/Gate on every protected operation.
- Session regenerates after login; logout/session invalidation, 401, 403 and 419 flows are tested.
- Password, token, cookie, authorization header, secret, private content and raw exception details are never logged.
- Secrets come from environment/secret management and are absent from source, docs, frontend bundles and build output.
- Public forms and sensitive Admin endpoints have configurable rate limits, anti-automation and server validation.
- Rich text, URLs, embeds, Page Builder JSON and `postMessage` payloads use allowlists and server-side validation/sanitization.
- CSP, HSTS in HTTPS environments, content-type, referrer, frame and other security headers are explicit. Page Builder preview framing is allowed only for the approved same origin.
- Dependency audit, static analysis and security review run before production cutover.
- Audit logs cover identity/permission changes, publish/delete/settings/media operations and other privileged mutations.

## Accessibility

- Public and Admin target WCAG 2.2 AA for applicable success criteria.
- All interactive controls are keyboard reachable with visible focus and a logical focus order.
- Dialogs, drawers, menus and Page Builder interactions manage focus, escape/close behavior and accessible names.
- Form fields have labels, instructions and programmatically associated error messages; validation is not color-only.
- Text/background and UI-component contrast meet AA; status uses text/icon in addition to color.
- Heading hierarchy, landmarks, table headers and semantic links/buttons are correct.
- Images require meaningful alt text or explicit decorative treatment.
- Responsive layouts work at 320 CSS pixels without hiding essential actions; data-heavy Admin views need a deliberate mobile pattern.
- Motion respects `prefers-reduced-motion` and avoids seizure-triggering effects.
- Automated accessibility scans plus keyboard/screen-reader smoke tests run on representative routes before UAT.

## Performance

- Public production pages target p75 Core Web Vitals: LCP ≤ 2.5 s, INP ≤ 200 ms and CLS ≤ 0.1 on representative mobile traffic.
- Cached public HTML should target server TTFB ≤ 800 ms in the production region; uncached paths must be measured and documented.
- Responsive image variants, intrinsic dimensions and lazy loading are used where appropriate; hero/LCP media is prioritized deliberately.
- Public Blade rendering avoids HTTP loopback, N+1 queries and unbounded collections.
- Published Page Builder documents/fragments use versioned cache keys and tag-based invalidation.
- Heavy image processing, email, sitemap, import/export and report work runs through queues with retry/timeout policy.
- Angular production builds define initial and component-style budgets; budget regression fails CI once the target Admin is scaffolded.
- API pagination, filter/sort allowlists and bulk limits prevent unbounded responses.

## SEO

- Public content is server-rendered Blade with crawlable HTML and stable canonical URLs.
- Every indexable page supports localized title, description, canonical and Open Graph/Twitter metadata as applicable.
- Sitemap, robots, redirects and structured data are generated from published source-of-truth records.
- Preview, draft, Admin, internal search states and non-public files are `noindex`/non-crawlable as appropriate.
- Structured data never emits a fake Product Offer when no valid public price exists.
- Slug changes use explicit redirects; duplicate locale/slug combinations are prevented by constraints.
- Error pages return correct HTTP status rather than soft-404 content.

## Observability and auditability

- Each request has a correlation/request ID propagated to API responses, logs and queued work where practical.
- Application logs are structured, levelled and redacted; production errors go to an approved error tracker/alert channel.
- Health checks distinguish application, database, cache/queue and storage dependencies without exposing secrets.
- Monitor HTTP error rate/latency, queue depth/failures, scheduler health, disk/storage capacity and backup status.
- Alerts have an owner, severity, deduplication and runbook link.
- Audit records include actor, action, target, timestamp, request context and safe before/after metadata when appropriate.
- Retention and deletion policies are configurable and documented before production.

## Backup and recovery

- Back up MySQL, public/private media metadata and required application configuration; never rely only on application-server disks.
- Backups are encrypted, access-controlled, integrity-checked and stored separately from the primary runtime.
- Initial engineering targets are RPO ≤ 24 hours and RTO ≤ 4 hours; P52 must confirm or replace them with owner-approved production values.
- Keep a documented retention schedule with at least daily recovery points; legal/business retention is confirmed before production.
- Run a restore test at least quarterly and before production cutover or major storage migration.
- A successful backup job is not proof of recoverability; restore evidence and elapsed time are recorded.

## Browser and device support

- Public website: latest two stable major versions of Chrome, Edge, Firefox and Safari, plus current iOS Safari and Android Chrome.
- Admin: latest two stable Chrome and Edge desktop versions, plus latest Firefox; other browsers follow Angular 22's official supported-browser policy.
- Internet Explorer is unsupported.
- Progressive enhancement preserves public navigation, reading and form fallback wherever practical.
- Responsive QA includes 320/375/768/1024 CSS-pixel viewports and representative desktop widths.

## File uploads and media safety

- Limits live in server configuration/settings and may vary by media class, endpoint and permission without a schema change.
- Proposed safe defaults: images ≤ 10 MiB, allowed documents ≤ 20 MiB, and video upload disabled until explicitly approved. Infrastructure ceilings may be stricter.
- Laravel enforces the effective limit; frontend validation is only an early user hint.
- Validate actual MIME/content, extension allowlist, size, image dimensions/decompression limits and authorization.
- SVG is blocked by default; enabling it requires a dedicated sanitizer and security tests.
- Generate server-side storage names; never trust client paths or use upload names as executable paths.
- Private originals stay outside the public web root or behind authorized delivery; variants are generated asynchronously when appropriate.
- Potentially risky document types require malware scanning/quarantine before availability.
- Upload failures, variant failures and deletions are observable and auditable without logging private file content.

## Reliability and data integrity

- Critical multi-table writes use transactions and idempotency/deduplication where double submit is plausible.
- Foreign keys, unique constraints and indexes enforce invariants in addition to application validation.
- Time is stored in UTC and displayed using `Asia/Ho_Chi_Minh` unless a user-specific timezone is explicitly introduced.
- Queue jobs define timeout, retry, uniqueness/idempotency and permanent-failure behavior.
- Published Page Builder versions are immutable; rollback creates a new version rather than mutating history.

## Local environment

- `hongvan.local` is reserved for this project in local WAMP usage.
- Its virtual host must point to `D:\www\HongVan\BackEnd\public`, never the repository or `BackEnd/` root.
- The existing WAMP entry is inspected and replaced only at the applicable runtime checkpoint; broader WAMP configuration and unrelated virtual hosts are preserved.
