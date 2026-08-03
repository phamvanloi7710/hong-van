# P19 — Public frontend template port report

## Status

`DONE`

## Scope completed

- Re-audited the current read-only WordPress clone: 558 files / 45,852,960 bytes / fingerprint `c8afab99d6faf61d181abfe7923eec196604d4c9`.
- Ported the reference visual structure into Laravel Blade SSR and shared design tokens.
- Added settings-backed header/footer/navigation, responsive home sections and reusable listing/detail/contact/content templates.
- Removed the e-commerce behavior boundary: no cart, checkout, payment, buyer account, wishlist, quick-buy, WooCommerce runtime or fake Offer.
- Added owner-requested self-hosted Bootstrap `5.3.8`, jQuery `4.0.0` and Font Awesome Free `7.3.1` through Vite.
- Completed `vi/en/zh` catalogs for every new UI string.
- Documented Page Builder block mapping and intentional public-domain deferrals.

## Source and asset decisions

- `FrontEndTemplate/` was not changed.
- No WordPress theme/plugin bundle was copied.
- No demo logo, promotional banner, external tracker or other-company content was copied.
- P19 uses controlled CSS artwork and Vite-hashed local vendor assets; approved Media IDs will replace presentation art later.

## Visual UAT

- Desktop 1440×900: three-column showcase, navigation, category panel and source-inspired green palette verified.
- Tablet 768×1024: hero/category reflow verified; `scrollWidth = clientWidth = 753`.
- Mobile 390×844: single-column reflow, Font Awesome and accessible menu toggle verified; `scrollWidth = clientWidth = 375`.
- Locales: `/`, `/en`, `/zh` returned `lang=vi/en/zh`, correct localized H1 and no horizontal overflow.

## Verification

- `npm install bootstrap@5.3.8 jquery@4.0.0 @fortawesome/fontawesome-free@7.3.1` — 0 vulnerabilities.
- `npm run build` — passed; CSS 324.09 KB raw / 53.71 KB gzip, JS 161.68 KB raw / 52.77 KB gzip; performance budget passed.
- `php artisan test --filter=PublicFrontendTest` — 9 passed, 96 assertions.
- `php artisan test` — 173 passed, 1,362 assertions.
- Initial parallel attempt encountered a MySQL schema-reset deadlock; no test process remained and the required sequential retry passed.
- Browser UAT on `http://hongvan.local/` — desktop/tablet/mobile and vi/en/zh passed.

## Database/API/UI changes

- Database: none.
- API: none.
- UI: complete P19 public template surface and reusable page contracts.

## Deferred items

- Business-data route binding, final Media assets, public form submission and Page Builder registry remain assigned to their existing prompts.
- P20 is next and was not executed in P19.
