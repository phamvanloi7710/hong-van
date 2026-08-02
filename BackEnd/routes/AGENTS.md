# AGENTS.md — ROUTES

- Reserved prefixes: `/admin`, `/api`, `/preview`, `/storage`, `/build`.
- Page catch-all đặt cuối cùng.
- Route admin SPA không được nuốt API/asset/public.
- Preview route signed, expiring, noindex.
- Route names ổn định và testable.
- Mọi admin API có auth/permission.
