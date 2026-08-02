# P06 Admin template mapping

## Source verification

The current read-only source still matches the P01 inventory: Annular `2.7.0`, Angular `20.1.3`, 258 files under `Template/src/app`, no `package-lock.json`, no configured `public/` directory and no root license proof.

## Mapping

| Reference area | Angular 22 target | P06 result |
|---|---|---|
| App shell and responsive drawer | `Admin/src/app/core/layout/admin-shell/` | Ported selectively |
| Header and toolbar controls | `Admin/src/app/core/layout/admin-header/` | Ported without demo widgets/data |
| Sidenav and nested menu | `Admin/src/app/core/layout/admin-sidebar/` | Ported with Hồng Vân feature placeholders |
| Horizontal menu | `Admin/src/app/core/layout/admin-horizontal-menu/` | Ported for desktop layout mode |
| Breadcrumb/content header | `Admin/src/app/core/layout/admin-breadcrumb/` and shell | Ported |
| Footer | `Admin/src/app/core/layout/admin-footer/` | Replaced purchase link with company/admin identity |
| Theme settings drawer | `Admin/src/app/core/layout/theme-settings-panel/` | Eight skins and layout controls ported |
| In-memory settings | `Admin/src/app/core/theme/` | Replaced by typed store and local adapter contract for P12 |
| Login visual pattern | `Admin/src/app/core/layout/auth-shell/` and `features/auth/login/` | Ported without demo credentials or fake auth |
| Dashboard/component showcase | `Admin/src/app/features/dashboard/` | Minimal foundation comparison page |
| Roboto and Material Icons | Fontsource npm packages | Self-hosted in the Angular bundle |
| Missing logo/avatars/demo images | None | Not copied; text/initial brand used |
| Demo API/data/pages/external links | None | Dropped |

## Responsive and visual checklist

- Desktop: 56px toolbar, 260/170/66px vertical menu modes, nested menu, content header, footer and settings drawer.
- Horizontal: 64px navigation row with dropdown groups.
- Tablet/mobile: breakpoint at 960px, overlay sidenav, compact header controls and single-column content.
- Settings: fixed header/sidebar/footer, RTL, vertical/horizontal menu, default/compact/mini density and eight original skin choices.
- Auth: `/admin/login` uses a separate responsive auth shell.
- Assets: no broken reference path and no request to template CDN/demo domains.

## Browser QA

- `1280x720`: vertical and horizontal dashboard layouts, nested menu, theme drawer, skin switching and local persistence passed.
- `390x844`: dashboard single-column layout, sidebar overlay and login layout passed without horizontal overflow.
- Login required-field validation passed; auth/API integration remains intentionally outside P06.
- Browser console had no warning/error and the rendered pages had no broken image.
- QA exposed and fixed the global Material Icons font binding and sidebar text/icon contrast.

Visual screenshots were generated as internal P06 QA artifacts and are not production source.
