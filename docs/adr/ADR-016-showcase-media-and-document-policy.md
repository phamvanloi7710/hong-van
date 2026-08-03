# ADR-016: Showcase media ownership and certification document policy

## Date

2026-08-03

## Status

Accepted for the Backend/Admin scope of P40. Public rendering and Page Builder registration remain deferred until the final frontend phase.

## Decision

- Store galleries/items, partners, certifications, projects and project media as explicit `hongvan_` tables with localized translations.
- Register every selected media reference in `hongvan_media_usages`; deleting referenced media is blocked until its business owner releases the reference.
- Require localized alt text for gallery media, partner logos and project media; localized captions remain optional.
- Treat certification documents as ready PDF media and store an explicit `document_visibility` value of `private` or `public`.
- The public showcase data source returns only currently published parent records. Private certification documents never expose their media ID or download label.
- Do not seed fabricated partners, certifications, projects, logos or case-study content.

## Consequences

- Administrators must enter verified company content and accessible labels before publication.
- Public Blade pages and Page Builder blocks can reuse one published-only, locale-aware and eager-loaded data contract later.
- Changing or removing showcase media remains consistent with the central Media Manager lifecycle and delete protection.
