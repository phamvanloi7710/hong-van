# P50 — Build reproducible và CI

## Status

`DONE`

## Scope completed

- Bổ sung bộ script PowerShell/Bash cho backend QA, Admin QA, aggregate verify, HTTP smoke và reproducible artifact.
- Tạo GitLab CI đúng remote thật của dự án, gồm secret/source policy, Laravel QA, Angular QA, optional Playwright E2E và artifact checksum.
- Cache Composer/npm download theo lockfile; không cache `vendor/`, `node_modules/`, secret hoặc build output.
- CI độc lập với source tham chiếu ignored và từ chối tracked source ngoài allowlist.
- Ghi hướng dẫn local, artifact, rủi ro dependency và branch protection.

## Files changed

- `.gitlab-ci.yml`
- `scripts/qa-backend.ps1`, `scripts/qa-backend.sh`
- `scripts/qa-admin.ps1`, `scripts/qa-admin.sh`
- `scripts/smoke.ps1`, `scripts/smoke.sh`
- `scripts/verify.ps1`, `scripts/verify.sh`
- `scripts/create-build-artifact.ps1`, `scripts/create-build-artifact.sh`
- `scripts/serve-spa.mjs`, `scripts/README.md`
- `docs/CI_AND_BUILD.md`
- `docs/adr/ADR-022-gitlab-reproducible-ci.md`, `docs/DECISIONS.md`
- `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`

## Database/API/UI changes

- Không thêm migration, bảng hoặc cột.
- Không đổi API contract hay Angular UI.
- Pipeline chỉ migrate database container `hongvan_ci`; local QA không migrate database WAMP `hongvan_platform`.
- Admin production bundle được build và sync lại theo chính sách sau thay đổi liên quan Admin.

## Commands and results

- PowerShell parser cho toàn bộ `scripts/*.ps1`: PASS.
- `bash -n` cho toàn bộ `scripts/*.sh`: PASS.
- `node --check scripts/serve-spa.mjs`: PASS.
- Ruby/Psych trong container đọc `.gitlab-ci.yml`: PASS. GitLab lint API ẩn danh trả 404 vì project private nên không được dùng làm bằng chứng giả.
- `scripts/verify.ps1 -SkipInstall -SkipReadonlySourceCheck -RunE2E -SmokeBaseUrl http://hongvan.local`:
  - prerequisites: PASS;
  - Composer validate/audit: PASS, không advisory;
  - prefix checker: PASS, 122 PHP files;
  - Pint: PASS;
  - Larastan: PASS, 425 files;
  - Laravel: 164 tests, 1266 assertions, PASS;
  - Angular lint: PASS;
  - Vitest: 26 files, 51 tests, PASS;
  - Angular production build/sync: PASS, 122 files;
  - Playwright: 14 tests, PASS;
  - smoke được chạy lại sau compatibility fix: `/health` 200, public ping 200, `/admin/` 200, unauth Admin API 401.
- `scripts/create-build-artifact.ps1 -SkipInstall` và Bash counterpart: PASS; 125 files, `sha256sum --check` PASS trên Git Bash.
- `git diff --check`: PASS.
- GitLab pipeline đầu tiên `#2726801250` xác nhận YAML hợp lệ nhưng Gitleaks chặn một fake Stripe key trong tài liệu đào tạo cũ; ignore được khóa theo fingerprint lịch sử duy nhất và full-history scan local được chạy lại trước pipeline kế tiếp.
- GitLab pipeline thứ hai `#2726814009` pass security/Admin nhưng phát hiện container backend thiếu GD WebP/AVIF và dùng `APP_ENV=ci` làm auth tests nhận 419; job được sửa sang `APP_ENV=testing`, bật encrypted session và compile đủ image codecs trước lần chạy kế tiếp.
- GitLab pipeline cuối `#2726831966` tại commit `2a51b87`: PASS trong 6 phút 12 giây; `secret_and_source_policy`, `admin_qa`, `backend_qa` và `web_asset_artifact` đều xanh.

## Risks

- `npm audit` đầy đủ còn 3 moderate và 2 high trong development toolchain; production audit không có vulnerability và critical gate pass. Không chạy auto-fix/breaking downgrade.
- PHP CI job build extension trên official PHP image nên chậm hơn custom prebuilt image; có thể tối ưu tại P51 mà không đổi quality gate.
- GitLab SaaS pipeline đã được xác minh end-to-end từ checkout sạch; optional E2E không bật mặc định vì local 14-test Playwright suite đã pass và job chỉ chạy khi `RUN_E2E=true`.

## Deferred / owner-controlled source

`verify-readonly-sources` phát hiện `FrontEndTemplate/` khác fingerprint cũ trên 558 files vì chủ dự án đang phát triển template. P50 không đọc sâu, sửa, xóa hoặc cập nhật fingerprint source này. Tùy chọn skip chỉ áp dụng cho lần QA được chủ dự án cho phép; default guard vẫn bật.

Theo chỉ đạo mới của chủ dự án, sau P50 sẽ chuẩn bị frontend public. Bước đầu tiên phải re-audit chính source `FrontEndTemplate/` hiện tại rồi xác định tiếp tục P18/P19; P51 vẫn là prompt chuẩn kế tiếp nhưng chưa được chạy.
