# P48 — QA backend toàn diện

**Status:** PARTIAL

**Date:** 2026-08-03

**Scope:** Backend Laravel hiện có; không chạy P49 và không thay đổi Admin/public frontend.

## Kết quả

- Full suite trên WAMP MySQL `hongvan_testing`: **164 tests, 1266 assertions, pass**.
- Xdebug coverage toàn backend: **81.5% line coverage**. Đây là baseline quan sát, không dùng làm chỉ tiêu phần trăm hình thức.
- Architecture suite mới: **13 tests, 16 assertions, pass**.
- Media queue chạy lại cùng operation đã hoàn tất không tạo lại variants/audit và không tăng attempts.
- Scheduled post publisher đã có behavior test chạy lặp `1` rồi `0`; schedule được xác nhận chạy mỗi phút với `withoutOverlapping`.
- Fresh migration, production-safe seed, rollback migration cuối, migrate lại và `migrate:status`: pass, không còn migration pending.
- Config cache, route cache và danh sách **217 routes**: pass; cache thử nghiệm đã được dọn sau kiểm tra.
- Pint, Larastan level 6, Composer audit và table-prefix checker: pass.

## Architecture gates

| Gate | Kết quả |
|---|---|
| Mọi bảng/column có comment | Pass qua `DatabaseCommentTest` |
| Prefix `hongvan_`, không connection prefix | Pass; checker đã quét 122 PHP files |
| Thin controller | Pass; không controller nào vượt ngưỡng 150 dòng, lớn nhất hiện tại 143 dòng |
| Blade không query database | Pass |
| Public data sources không lộ draft | Pass cho crop solutions, posts, search, related content, services, showcase, sitemap, transportation và warehouses |
| Page Builder không thực thi renderer tùy ý | Static guard pass; cấm request/document-driven view, `Blade::render`, `eval`, process execution, dynamic include/new và unsafe unserialize |

## Critical-domain coverage

Coverage được đánh giá theo behavior quan trọng và số test method hiện có, kết hợp full-suite coverage; không tăng test chỉ để đạt một con số tổng.

| Domain | Test methods | Hành vi chính đã được bảo vệ |
|---|---:|---|
| Auth/RBAC | 22 | login/logout/session, active/locked user, permission precedence, authorization, bootstrap super admin |
| Media | 7 | MIME/upload, malicious files, variants, permissions, usage/delete, retry và queue idempotency |
| Leads | 5 | consent/honeypot/rate limit, deduplication, encrypted data, assignment/status/notes/export |
| Products/pricing | 13 | CRUD/policy, locale/taxonomy, fixed/from/range/contact/hidden/null/zero price behavior; có data-provider scenarios |
| SEO | 13 | metadata escaping/fallback, redirects, sitemap visibility, structured data và Offer policy |
| Post publish | 4 | sanitize, permission, scheduled publish idempotency, draft exclusion, slug history và eager loading |
| Page Builder publish | 0 | Deferred: P21-P31 chưa được triển khai |

## Commands and actual results

- `php artisan test` → 164 tests, 1266 assertions, pass.
- `$env:XDEBUG_MODE='coverage'; php artisan test --coverage --min=0` → pass, total line coverage 81.5%.
- `vendor\\bin\\pint --test` → pass.
- `vendor\\bin\\phpstan analyse --memory-limit=1G` → pass, Larastan level 6, 425 files.
- `composer audit` → no security vulnerability advisories.
- `php ..\\scripts\\check-table-prefix.php` → pass, 122 PHP files.
- `php artisan migrate:fresh --seed --env=testing` → pass on explicitly verified `hongvan_testing`.
- `php artisan migrate:rollback --step=1 --env=testing` → pass.
- `php artisan migrate --env=testing` and `php artisan migrate:status --env=testing` → pass, every migration `Ran`.
- `php artisan config:cache --env=testing` and `php artisan route:cache --env=testing` → pass.
- `php artisan optimize:clear --env=testing` → pass after cache QA.

## Changes and root cause

- Added regression architecture gates so later code cannot silently introduce oversized controllers, DB access in Blade, arbitrary Page Builder execution or public draft leakage.
- `GenerateMediaVariants` previously regenerated an already completed operation if a queue message was delivered twice. The job now returns immediately for `completed` operations; its feature test executes the same job twice and verifies stable variants, attempts and audit count.

## Database/API/UI

- No migration, table or column change.
- No API contract or route change.
- No Angular/public UI change; Angular build/sync is not applicable to P48.

## Remaining risk / deferred

- Page Builder P21-P31 has not been implemented, so publish/version/rollback/preview behavior cannot be covered yet. Only its source-level arbitrary-renderer guard is active.
- Public frontend remains intentionally last per owner decision; this QA does not claim public-template coverage.

## Next prompt

`P49 — Angular E2E and visual QA`. This report stops before P49.
