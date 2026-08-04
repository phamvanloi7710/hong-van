# ADR-028: Versioned Page Templates, Safe Import/Export and Expiring Edit Locks

- Status: Accepted
- Date: 2026-08-04

## Decision

Reusable Page Builder templates keep an immutable published document version and are grouped by database-backed categories. New pages and duplicated pages receive a fresh draft document with regenerated block IDs and reset publication state.

Export uses a versioned JSON manifest with only schema, block-version and media `public_id` metadata. Import passes the server document migrator and allowlisted block validator before persistence; it never imports executable content, private media URLs or arbitrary views.

Editing uses one expiring database lock per page. The raw token is returned only to the owner and persisted as a hash. Heartbeats extend a five-minute TTL; expiry recovers from crashed browser sessions. Force unlock is independently permission-gated and audited.

## Consequences

- Imported documents remain subject to the same renderer, sanitizer and media-availability rules as locally authored documents.
- A stale or different session cannot overwrite a page while an active lock exists.
- Operators can recover an abandoned edit lock without introducing permanent locks.
