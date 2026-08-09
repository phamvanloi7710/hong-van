# TEST MATRIX

Đối chiếu tại T011: các lệnh dưới đây đều tồn tại trong manifest hoặc script ở HEAD. Runtime local chuẩn là Docker; không dùng WAMP. Các wrapper `scripts/*.ps1` và `scripts/*.sh` là entrypoint CI/host hiện hữu, chỉ chạy khi host có đủ PHP/Composer/Node hoặc từ container tương ứng.

## Nguồn lệnh chuẩn

| Phạm vi | Lệnh hiện hữu | Nguồn |
|---|---|---|
| Backend test | `composer test`, `php artisan test` | `BackEnd/composer.json` |
| Backend static/format | `composer analyse`, `vendor/bin/pint --test` | `BackEnd/composer.json`, `scripts/qa-backend.*` |
| Database contract | `php scripts/check-table-prefix.php` | `scripts/check-table-prefix.php` |
| Public assets | `npm run build`, `npm run budget` | `BackEnd/package.json` |
| Admin lint/test/build | `npm run lint`, `npm test -- --watch=false`, `npm run build:laravel` | `Admin/package.json` |
| Admin E2E | `npm run e2e:media`, `npm run e2e:accessibility`, `npm exec -- playwright test` | `Admin/package.json`, `Admin/playwright.config.ts`, `Admin/e2e/*.spec.ts` |
| Toàn repository | `scripts/verify.ps1` hoặc `scripts/verify.sh` | `scripts/verify.*` |
| Artifact | `scripts/create-build-artifact.ps1` hoặc `scripts/create-build-artifact.sh` | `scripts/create-build-artifact.*` |

Artisan tùy biến đã đối chiếu: `identity:bootstrap-super-admin`, `leads:anonymize-expired`, `media:cleanup`, `media:retry`, `pages:publish-scheduled`, `posts:publish-scheduled`, `search:reindex`. Không dùng tên lệnh ngoài danh sách `php artisan list` hiện tại.

Image runtime production cài dependency với `--no-dev`, nên không chạy `php artisan test`, PHPUnit, Pint hoặc PHPStan trực tiếp trong container app đang phục vụ. Các lệnh QA backend phải chạy trong CI/QA image hoặc container mount source có dev dependencies; không cài dev package vào runtime image.

## Fast gate theo thay đổi

| Thay đổi | Gate tối thiểu |
|---|---|
| Chỉ tài liệu/prompt | `php scripts/hongvan-master-v2/validate.php`; `git diff --check` |
| Backend PHP | PHPUnit file/filter liên quan; `vendor/bin/pint --test`; `composer analyse` khi đổi contract/type/query |
| Migration/schema | test migration liên quan trên `hongvan_testing`; `php scripts/check-table-prefix.php`; fresh/rollback theo task |
| Admin Angular | `npm run lint`; `npm test -- --watch=false`; bắt buộc `npm run build:laravel` |
| Public Blade/Vite | Feature test liên quan; `npm run build` trong `BackEnd/` |
| Workflow trình duyệt | Playwright spec liên quan với `PLAYWRIGHT_BASE_URL=http://hongvan.local/admin/` |
| Runtime local | `scripts/smoke.ps1 -BaseUrl http://hongvan.local` hoặc bản Bash tương ứng |

## Full gate

- Backend: `scripts/qa-backend.ps1 -SkipInstall` hoặc `scripts/qa-backend.sh --skip-install`. Chỉ thêm migration gate với `APP_ENV=testing` và `DB_DATABASE=hongvan_testing`; không migrate/reset `hongvan_platform`.
- Admin: `scripts/qa-admin.ps1 -SkipInstall -RunE2E` hoặc `scripts/qa-admin.sh --skip-install --run-e2e`.
- Toàn repository: `scripts/verify.ps1 -SkipInstall -RunE2E -SmokeBaseUrl http://hongvan.local` hoặc bản Bash tương ứng.
- Release artifact: `scripts/create-build-artifact.ps1 -SkipInstall` hoặc bản Bash tương ứng.
- Playwright discovery trước khi chạy E2E: `npm exec -- playwright test --list` trong `Admin/`.

Full suite bắt buộc tại các phase gate T224-T228 và vòng tái chứng nhận T235-T240. Không bỏ test, nới budget, reset database ứng dụng hoặc thêm ignore rộng chỉ để gate xanh.
