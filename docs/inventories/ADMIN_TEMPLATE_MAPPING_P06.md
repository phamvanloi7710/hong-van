# P06 Admin template mapping

## Source verification

The refreshed read-only source was re-checked against the live Annular demo. It remains Annular `2.7.0`, Angular `20.1.3`, with no `package-lock.json`, configured `public/` directory or root license proof. The previous selective visual port was replaced by a full-fidelity shell/dashboard/auth re-port while preserving HongVan auth, permission and business routes.

## Mapping

| Reference area | Angular 22 target | P06 result |
|---|---|---|
| App shell and responsive drawer | `Admin/src/app/core/layout/admin-shell/` | Re-ported to Annular desktop/mobile dimensions |
| Header and toolbar controls | `Admin/src/app/core/layout/admin-header/` | Re-ported with Annular control order and behavior-safe local actions |
| Sidenav and nested menu | `Admin/src/app/core/layout/admin-sidebar/` | Re-ported with Annular profile/menu geometry and Hồng Vân permissions |
| Horizontal menu | `Admin/src/app/core/layout/admin-horizontal-menu/` | Ported for desktop layout mode |
| Breadcrumb/content header | `Admin/src/app/core/layout/admin-breadcrumb/` and shell | Ported |
| Footer | `Admin/src/app/core/layout/admin-footer/` | Replaced purchase link with company/admin identity |
| Theme settings drawer | `Admin/src/app/core/layout/theme-settings-panel/` | Eight skins and layout controls ported |
| In-memory settings | `Admin/src/app/core/theme/` | Replaced by typed store and local adapter contract for P12 |
| Login visual pattern | `Admin/src/app/core/layout/auth-shell/` and `features/auth/login/` | Re-ported to centered Annular card; real Sanctum auth preserved |
| Dashboard/component showcase | `Admin/src/app/features/dashboard/` | Rebuilt with Annular tiles, gradient charts, panels and responsive grid |
| Roboto and Material Icons | Fontsource npm packages | Self-hosted in the Angular bundle |
| Missing logo/avatars/demo images | None | Not copied; text/initial brand used |
| Demo API/data/pages/external links | None | Dropped |

## Responsive and visual checklist

- Desktop: 56px toolbar, 260/170/66px vertical menu modes, 181px profile block, 123px content header, nested menu, footer and settings drawer.
- Horizontal: 64px navigation row with dropdown groups.
- Tablet/mobile: breakpoint at 960px, overlay sidenav, compact header controls and single-column content.
- Settings: fixed header/sidebar/footer, RTL, vertical/horizontal menu, default/compact/mini density and eight original skin choices.
- Auth: `/admin/login` uses the Annular-centered 386px card and 168px patterned header.
- Assets: no broken reference path and no request to template CDN/demo domains.

## Browser QA

- `1280x720`: toolbar `56px`, sidebar `260px`, content header `123px`, first tile `150x69px`, first info card `233x141px`; dashboard, identity route and local persistence passed.
- `390x844`: two-column tile layout, single-column info cards, 280px overlay sidebar and theme drawer passed with document `scrollWidth = 390`.
- Theme switch to teal and reset to indigo were verified in-browser; toolbar changed `rgb(0, 105, 92)` then returned to `rgb(40, 53, 147)`.
- Login required-field validation passed; auth/API integration remains intentionally outside P06.
- Browser console had no warning/error and the rendered pages had no broken image.
- QA exposed and fixed the global Material Icons font binding and sidebar text/icon contrast.

Visual screenshots were generated as internal P06 QA artifacts and are not production source.
