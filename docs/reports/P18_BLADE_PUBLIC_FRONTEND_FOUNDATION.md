# P18 — Blade public frontend foundation

## Status

DONE — 2026-08-03.

## Scope completed

- Replaced the Laravel welcome screen with a settings-backed Blade SSR shell.
- Added semantic header/main/footer, skip link, responsive design tokens and accessible focus/reduced-motion behavior.
- Added public Blade primitives for buttons, links, Media images, headings, containers, breadcrumbs, alerts and form fields.
- Added home, privacy, terms, 404 and 500 views with matching `vi`, `en`, `zh` catalogs.
- Added canonical redirects from `/vi`, `/vi/privacy` and `/vi/terms` to unprefixed default-locale routes.
- Re-audited the 558-file WordPress clone and documented normalization into Laravel/Vite.
- Explicitly excluded WooCommerce, cart, checkout, payment, customer account, wishlist and quick-buy behavior.

## Database / API / UI

- Database: no migration or schema change.
- API: no new endpoint or response-contract change.
- UI: neutral P18 foundation only; P19 owns visual porting from `FrontEndTemplate/`.

## Commands and evidence

- `npm run build`: PASS; Vite 7.3.6, CSS 7.64 KB, JS 0.06 KB, performance budget PASS.
- `php artisan test --filter='PublicFrontend|LocaleFoundation'`: PASS; 15 tests, 117 assertions.
- `php artisan test`: PASS; full backend suite 170 tests, 1,328 assertions.
- `vendor/bin/pint --test`: PASS.
- `vendor/bin/phpstan analyse app/Domain/PublicSite app/Http/Controllers/PublicSite --no-progress`: PASS, no errors.
- Browser `http://hongvan.local/`, `/en`, `/zh/privacy`: PASS; correct language/text, loaded hashed assets, no horizontal overflow at 390x844 and zero console errors.
- `git diff -- FrontEndTemplate`: empty; source reference remained read-only.

## Operational note

`BackEnd/public/.htaccess` configures one-year immutable cache for `/build/assets/`. WAMP `mod_headers` was enabled in Apache config and passed `httpd -t`; the currently running service could not be restarted through the non-elevated service controller. The rule becomes active on the next normal WAMP restart. Asset versioning is already active through Vite content hashes.

## Deferred

- Visual fidelity, selected asset porting and full section-to-Page-Builder mapping belong to P19.
- No frontend template files were copied or modified in P18.
