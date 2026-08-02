# ADMIN TEMPLATE INVENTORY

## Status

`READY` — source hiện tại tại `Template/` đã được kiểm tra lại read-only ngày 2026-08-02 và đối chiếu trực tiếp với demo `annular.themeseason.com`. Package vẫn thiếu `package-lock.json`, thư mục asset `public/` và license file ở root, nhưng source layout/theme đủ để port giao diện vào `Admin/`.

## Re-audit after template refresh

- Source hiện tại vẫn là Annular `2.7.0`, Angular `20.1.3`; manifest và cấu trúc shell khớp demo đang chạy.
- Chuẩn desktop đo trực tiếp trên demo: toolbar `56px`, sidebar `260px`, user block khoảng `181px`, content header `123px`, tile `150x69px` và info card `233x141px` tại viewport `1280x720`.
- Chuẩn login: card `386px`, header `168px`, căn giữa viewport.
- `Template/public/` vẫn không tồn tại nên logo/avatar/header background gốc không có trong package; target dùng branding Hồng Vân và CSS pattern tương đương, không lấy asset từ website demo.
- `git diff -- Template` phải tiếp tục bằng 0 sau khi port.

## Identity and toolchain

| Item | Actual source evidence |
|---|---|
| Template | Annular 2.7.0 |
| Angular | Core, CLI, Material, CDK and build packages 20.1.3 |
| TypeScript / RxJS | 5.8.3 / 7.8.2 |
| Entry point | `src/main.ts` bootstraps standalone `AppComponent` with `appConfig` |
| Build target | `@angular/build:application` |
| Default build output | `dist/annular` |
| Unit test runner | Karma/Jasmine through `ng test` |
| Installed dependencies | No `Template/node_modules/` directory |
| Lockfile | Missing |

Manifest dependencies also include `@ngbracket/ngx-layout` 20.0.0, Angular Google Maps 20.1.3, ngx-charts 22.0.0, ngx-datatable 21.1.0, angular-calendar 0.31.1, ngx-quill 28.0.1 with Quill 2.0.3, ngx-scrollbar 18.0.0, Dragula integrations, Leaflet and Zone.js 0.15.1.

## Build commands

Commands declared in `Template/package.json`:

- `npm run start` → `ng serve`.
- `npm run build` → `ng build`.
- `npm run watch` → development watch build.
- `npm test` → `ng test` with Karma/Jasmine.

There is no lint script, no E2E package and no `*.spec.ts` source file. P01 did not install dependencies or run build/test commands. `angular.json` does not set `/admin/` as `baseHref` and does not target `BackEnd/public/admin`; both belong to later controlled integration, not this read-only source.

## Structure

| Area | Observed content |
|---|---|
| `src/app/common/` | Demo menu/user data, models and reusable pipes |
| `src/app/pages/` | Shell plus dashboard, users, UI/form/table demos, mailbox, chat, profile, schedule, maps, charts, landing, login/register and error pages |
| `src/app/services/` | Demo/in-memory services for users, chat, menu, tables, messages, landing and related samples |
| `src/app/theme/` | Sidenav, vertical/horizontal menus, toolbar widgets, content header, breadcrumb, SCSS theme layers, skins and utilities |

The source contains 258 files under `src/app/`. It has no project business domains, permission layer, real authentication service, API environment contract, Help Center or Page Builder implementation.

## Entry point and providers

- `main.ts` uses `bootstrapApplication` and standalone components.
- `app.config.ts` configures router preloading, async animations, HttpClient, Angular Calendar and `angular-in-memory-web-api` with a one-second fake user API delay.
- `PreloadAllModules` means lazy route boundaries exist but are preloaded after startup.
- `main.ts` assigns `(window as any).global = window` for Dragula compatibility; this requires review in the target strict workspace.

## Layout and responsive behavior

- `pages.component.html` supplies the Annular shell: top toolbar, vertical sidenav, optional horizontal menu, router outlet, settings drawer and footer.
- Layout settings support fixed header/sidenav/footer, vertical or horizontal menu, default/compact/mini menu, RTL and skin choice.
- The settings exist only in memory; no persistence service or API contract is present.
- At viewport width `<= 960px`, `pages.component.ts` forces the vertical menu and closes/unpins the sidenav.
- Header widgets include search, favorites, language flag, fullscreen, applications, messages and user menu. These are demo widgets and require explicit mapping before reuse.
- The footer contains a ThemeForest purchase link and the menu contains `themeseason.com` links; these must not be ported.

## Routing and menu

- `app.routes.ts` exposes the main shell, landing, login, register and error pages with no authentication guard.
- Child routes cover dashboard, users, UI controls, dynamic menu, mailbox, chat, form controls, tables, profile, schedule, maps, charts, drag/drop, icons, blank and search pages.
- Vertical and horizontal menus are static demo arrays. Menu models have no permission field, and route definitions contain no authorization metadata.
- No Hồng Vân product, transport, warehouse, CMS, lead, RBAC, audit, Media or Page Builder route exists in this template.

## Theme, icons, fonts and assets

- Angular Material uses the `azure-blue` prebuilt theme plus custom base, spacing, library override, theme, gradient and RTL SCSS layers.
- Eight skins exist: blue-dark, gray-dark, gray-light, green-dark, indigo-light, pink-dark, red-light and teal-light.
- Material Icons are used throughout; the toolbar also contains inline SVG for the pin control.
- Global styles load Roboto, Material Icons and Quill 1.2.2 stylesheets from external Google/CDN URLs while the package uses Quill 2.0.3.
- `angular.json` expects a root `public/` directory, but none exists. The source references `favicon.ico`, `img/logo.png`, avatars, flags, profiles and landing images that are not supplied.

## Authentication screens

- Login and registration are English demo reactive forms.
- Login only validates fields and navigates to `/`; it performs no authentication request.
- Registration only validates fields and navigates to `/login`; it creates no account.
- The main admin route is unprotected. There is no auth guard, permission guard, interceptor, CSRF flow or session handling.

Only the screen layout may be adapted. Hồng Vân must implement same-origin Sanctum cookie/session, CSRF, authorization and permission-aware routing in later prompts.

## Reusable candidates

- Authenticated shell layout after real auth is added.
- Sidenav and vertical/horizontal menu interaction.
- Theme/skin architecture and responsive layout controls.
- Content header, breadcrumb and generic Material form/table presentation patterns.
- Selected toolbar interactions only when a real Hồng Vân requirement exists.

## Items to drop by default

- In-memory user API and all sample data.
- Public registration link/form for the Admin application.
- Landing, mailbox, chat, dynamic menu, schedule, map, chart, icon and generic demo pages unless a later approved module maps them.
- ThemeForest purchase link, `themeseason.com` links, Annular branding and missing demo image paths.

## Angular 22 migration risks

1. The source is Angular 20.1.3 while the locked target is 22.1.x, a two-major-line gap. Port into the future Angular 22 target rather than treating this folder as production source.
2. Third-party peer compatibility must be verified for ngx-layout, carousel, charts, datatable, calendar, Quill, scrollbar, Dragula and Leaflet integrations.
3. No lockfile exists, so the exact dependency graph is not reproducible.
4. No unit tests are supplied to detect migration regressions.
5. Missing `public/` assets prevent a complete visual comparison and may block a clean build.
6. The target requires strict TypeScript; the template still uses explicit `any` and disables `strictPropertyInitialization`.
7. Remote fonts/icons/editor CSS require CSP, privacy, offline and version review.

## License note

No root `LICENSE`, `LICENCE`, `COPYING` or `NOTICE` file is supplied. Treat `Template/` as licensed read-only reference material and confirm reuse/redistribution rights before P06 copies any code or asset.
