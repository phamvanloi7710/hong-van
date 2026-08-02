# SOURCE MAPPING SUMMARY

## Status

Initial P01 mapping based on the replacement Admin template currently on disk. No reference source was copied or modified.

## Mapping

| Reference | Target | Action | Status |
|---|---|---|---|
| Annular shell, sidenav and menu interaction | `Admin/` | Port selectively into the Angular 22 target | READY FOR DESIGN |
| Theme/skin SCSS and layout controls | `Admin/` | Adapt to Hồng Vân branding and persisted preferences | NEEDS CONTRACT |
| Content header, breadcrumb and generic Material presentation patterns | `Admin/` | Port selectively with responsive/accessibility verification | READY FOR DESIGN |
| Static demo menu/routes | `Admin/` | Replace with Hồng Vân feature routes and permission metadata | REPLACE |
| Login screen layout | `Admin/` | Reuse visual pattern only; implement Sanctum cookie/session and CSRF | NEEDS CONTRACT |
| Register screen | None | Do not port unless an explicit admin onboarding requirement is approved | DROP |
| In-memory Web API and demo data/services | None | Do not port | DROP |
| Landing, mailbox, chat, schedule, maps, charts, UI, form/table, icon and dynamic-menu demos | None by default | Exclude unless a later approved requirement maps them | DROP |
| Annular branding, ThemeForest purchase link and external demo links | None | Remove in target code; never edit the reference source | DROP |
| Public frontend template | `BackEnd/resources/views/` and public Page Builder blocks | Inventory and port after source supply | DEFERRED — SOURCE MISSING |
| StayHub Media source | Hồng Vân Media domain and `Admin/` Media UI | Port workflow onto P16 storage/policy contract; remove tenant/property/domain/token coupling | READY — PORTED P17 |

## Decisions required before integration

1. Confirm the Admin template license and reuse rights.
2. Define the Angular 20.1.3-to-22.1.x selective-port strategy and verify third-party compatibility.
3. Define same-origin Sanctum cookie/session, CSRF, versioned Admin API and permission contracts.
4. Decide which fonts/icons/editor styles are self-hosted and which external origins, if any, CSP permits.
5. Obtain the missing Admin `public/` assets or formally approve replacements before visual clone acceptance.
6. Supply the licensed public frontend template and rerun its deferred inventory. StayHub Media was supplied, re-inventoried and ported in P17.
7. At the appropriate local-runtime setup checkpoint, remove the existing WAMP entry for `hongvan.local` and recreate it for this project, pointing the web root to `BackEnd/public/`. P01 does not edit WAMP configuration.

## Next checkpoint

P02 may record architecture and delivery decisions. P01 does not execute P02.
