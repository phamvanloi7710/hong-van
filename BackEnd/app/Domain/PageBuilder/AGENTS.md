# AGENTS.md — PAGE BUILDER DOMAIN

- Server registry là nguồn chân lý.
- Document JSON phải có schemaVersion và block version.
- Không nhận Blade view/class name từ DB.
- Không eval.
- Rich text sanitize.
- Bindings chỉ qua DataSourceRegistry allowlist.
- Published versions immutable.
- Preview Redis TTL + signed token + ownership.
- Cache key gồm page/locale/page version/theme version.
- Mỗi block phải có renderer, schema, defaults, migration và tests.
