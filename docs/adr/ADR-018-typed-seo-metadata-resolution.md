# ADR-018: Typed SEO metadata and deterministic resolution

- Status: Accepted
- Date: 2026-08-03
- Prompt: P42

## Context

Public content already has some localized `meta_title` and `meta_description` preparation fields, while canonical, robots and social-sharing metadata need one consistent model. Storing PHP class names in a generic morph type would expose implementation details and make request-controlled type resolution unsafe.

## Decision

- Store locale-specific metadata in `hongvan_seo_meta` using a stable allowlisted `seoable_type`, internal `seoable_id` and unique entity/locale constraint.
- Resolve metadata in this order: entity SEO record, future page-level metadata, legacy localized entity fields, global company defaults.
- Treat the generated public route URL as canonical unless an administrator with `seo.update` saves a validated absolute HTTP/HTTPS override.
- Force `noindex, nofollow` for draft, preview and Admin contexts. Published public pages also respect the global indexing switch and per-record robots settings.
- Use only ready public Media Manager images, track their usage, and prefer the largest ready image variant for Open Graph/Twitter output.
- Keep legacy localized SEO fields as compatibility fallbacks; new canonical/social metadata is managed through the typed SEO record.

## Consequences

- New entity types must be explicitly added to `SeoEntityRegistry` before the Admin API can address them.
- Public Blade route integration will call `SeoMetaResolver` and the shared escaped `<x-seo.meta>` component when the postponed public frontend is implemented.
- Arbitrary morph classes, arbitrary canonical schemes and duplicate head tags are not accepted.
