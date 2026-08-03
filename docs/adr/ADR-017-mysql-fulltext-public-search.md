# ADR-017: MySQL FULLTEXT for public content search

## Date

2026-08-03

## Status

Accepted for the Backend scope of P41. Public Blade SSR and the Page Builder search block remain deferred until the final frontend phase.

## Decision

- Use the existing MySQL 9.1 InnoDB database and native FULLTEXT indexes instead of adding Laravel Scout or an external search service.
- Search only active-locale translations belonging to currently published, non-deleted products, crop solutions, services, posts and projects.
- Maintain an application-generated `search_text` field for each searchable translation. It uses Unicode decomposition, removes combining marks, maps Vietnamese `đ` to `d`, preserves non-Latin characters and feeds a dedicated FULLTEXT index.
- Build Boolean FULLTEXT expressions only from normalized Unicode letter/number tokens and bind them as query parameters. Content type filters and page sizes are allowlisted.
- Return escaped, allowlisted `<mark>` highlights. Raw content or request markup is never emitted.
- Derive related content only from explicit categories, tags, crops or crop stages. Projects return no automatic relation until an explicit project taxonomy exists.
- Keep search analytics disabled by default. When enabled, redact email/phone-like terms and store only a keyed visitor hash; never store raw IP or user agent.
- Expose operational verification through `php artisan search:reindex --health`. MySQL maintains FULLTEXT indexes automatically, so the command verifies index presence and the `EXPLAIN` access plan without performing a disruptive rebuild.

## Evidence

- Runtime database: MySQL 9.1, InnoDB, `utf8mb4_0900_ai_ci`.
- The content scale is currently small and the application already owned a native FULLTEXT index for post translations.
- Runtime `EXPLAIN` reports `access=fulltext` and the expected P41 index for all five searchable translation tables.

## Consequences

- No new Composer package, queue-backed indexing service or external infrastructure is required.
- Existing and future localized records are backfilled/maintained consistently for accent-folded search.
- A future move to Scout requires measured scale or search-quality evidence and a replacement ADR.
- Public Blade rendering must call `PublicSearchQuery` directly rather than making an HTTP loopback to the public API.
