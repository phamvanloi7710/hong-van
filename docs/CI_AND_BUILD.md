# CI AND REPRODUCIBLE BUILD

## Pipeline đang dùng

Repository chính nằm trên GitLab, vì vậy `.gitlab-ci.yml` là pipeline thực thi. Pipeline dùng các stage `security`, `quality`, `e2e` và `artifact`:

1. `secret_and_source_policy`: quét lịch sử Git bằng Gitleaks và từ chối source có license bị commit từ `Template/`, `FrontEndTemplate/`, `SourceIntegrations/` ngoài các file hướng dẫn được allowlist.
2. `backend_qa`: PHP 8.5, MySQL 8.4 và Redis 7.4; validate/audit/install theo `composer.lock`, migrate database CI, prefix check, Pint, Larastan và Laravel test.
3. `admin_qa`: Node 24.15; chỉ dùng `npm ci`, audit, lint, Vitest và Angular production build/sync.
4. `admin_e2e`: chỉ chạy khi pipeline variable `RUN_E2E=true`; build production bundle, phục vụ `/admin/` bằng Node core HTTP server và chạy Playwright.
5. `web_asset_artifact`: build Vite public và Angular Admin, xác minh SHA-256 rồi lưu artifact 14 ngày.

Mọi job dừng ngay khi prefix, migration, audit theo ngưỡng, test hoặc build lỗi. Không có secret production trong pipeline; `APP_KEY` cố định chỉ dành cho container CI tạm thời.

## Lockfile và cache

- PHP cài đúng `BackEnd/composer.lock`; cache chỉ chứa Composer download cache, không cache `vendor/`.
- Admin và public assets cài đúng `package-lock.json` bằng `npm ci`; cache chỉ chứa npm download cache, không cache `node_modules/` hoặc output build.
- Cache key chứa runtime version và hash lockfile qua `cache:key:files`.
- Artifact chỉ chứa `BackEnd/public/build` và `BackEnd/public/admin/browser` cùng `SHA256SUMS.txt`; `.gitignore` loại toàn bộ output này khỏi commit.

## Chạy local

Windows đầy đủ trên dependency hiện có:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/verify.ps1 -SkipInstall -RunE2E -SmokeBaseUrl http://hongvan.local
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/create-build-artifact.ps1 -SkipInstall
```

Linux/macOS/Git Bash:

```bash
bash scripts/verify.sh --skip-install --run-e2e --smoke-base-url http://hongvan.local
bash scripts/create-build-artifact.sh --skip-install
```

Muốn kiểm tra migration local phải dùng database test riêng, ví dụ `APP_ENV=testing` và `DB_DATABASE=hongvan_testing`. Không chạy `--run-migrations` với database `hongvan_platform`.

Kiểm tra fingerprint source tham chiếu mặc định bật. Nếu chủ dự án đang tự thay `FrontEndTemplate/`, có thể thêm `-SkipReadonlySourceCheck` (PowerShell) hoặc `--skip-readonly-source-check` (Bash); CI vẫn độc lập với source ignored và vẫn chặn file source tham chiếu bị commit ngoài allowlist.

## Branch protection khuyến nghị

Khi chuyển sang quy trình Merge Request ổn định, cấu hình GitLab cho `main`:

- Protected branch: chỉ Maintainer được merge, không cho force push.
- Bắt buộc pipeline thành công trước merge và bỏ qua pipeline cũ khi có commit mới.
- Tối thiểu một approval từ người không phải tác giả; reset approval khi source branch thay đổi.
- Không cho merge khi còn discussion chưa resolve.
- Bắt buộc squash hoặc commit history tuyến tính theo quy ước của dự án.
- Chỉ cho deployment production từ protected branch/tag và protected CI variables.
- Secret production lưu bằng masked, hidden, protected CI/CD variables hoặc secret manager; không ghi vào YAML/artifact/log.

Hiện chủ dự án yêu cầu Codex commit/push trực tiếp `main`. Khi áp dụng rule “Merge Request only”, chính sách giao việc này cần đổi đồng thời để tránh xung đột.

## Rủi ro dependency hiện tại

`npm audit` đầy đủ đang báo 3 moderate và 2 high trong toolchain development. Pipeline vẫn fail với lỗ hổng production mức high trở lên và mọi lỗ hổng critical; không tự chạy `npm audit fix` vì có thể làm thay đổi Angular toolchain ngoài prompt. Cần xử lý bằng nâng cấp có kiểm thử ở prompt bảo mật/dependency riêng.
