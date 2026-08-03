# ADR-015: Post content sanitization and scheduled publishing

## Status

Accepted for the Backend/Admin scope of P39. Public rendering remains deferred until the final frontend phase.

## Decision

- Store localized post HTML only after server-side allowlist sanitization.
- Remove executable or embedded content (`script`, `style`, `iframe`, `object`, `embed`, forms), inline event/style attributes and unsafe URL schemes.
- Keep current localized slugs unique and reserve old slugs in `hongvan_post_slug_histories` for future canonical redirects.
- Publish scheduled posts through the idempotent `posts:publish-scheduled` command, registered every minute with overlap protection.
- Public data sources return only posts whose publication window is currently valid and eager-load all card/detail relations.
- Admin preview is informational; the Backend sanitizer remains the source of truth.

## Consequences

- Rich text cannot execute arbitrary scripts or embeds.
- The production scheduler must run Laravel `schedule:run` every minute.
- Public Blade routes, RSS and Page Builder registration will reuse the prepared data-source contract when the frontend foundation is available.
