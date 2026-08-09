# BASELINE GOVERNANCE GATE

## Snapshot được nghiệm thu

- Thời điểm: `2026-08-09T22:19:12+07:00`.
- Base HEAD: `c9ce11c4e6a5c2c6bcaac9a61b40e6be5b153fcf`; local và `origin/main` trùng nhau trước T012.
- T001-T011: `DONE` hoặc `VERIFIED`; không có dependency `FAILED`, `BLOCKED` hay `BLOCKED_EXTERNAL`.
- Master queue: đủ 240 task; status trong file master là catalog bất biến, trạng thái thực thi nằm trong `state/STATE.json`.
- Generated queue: `EMPTY`, 0 task, `audit_recheck_required=false`.
- T013 được mở vì dependency T012 hoàn tất và không còn mâu thuẫn baseline chưa xử lý.

## Gate chạy lại

| Nhóm | Kết quả |
|---|---|
| Pack/queue/state | PASS: 240 task, 240 state entry, generated queue rỗng |
| Rule/source boundary | PASS: 28 AGENTS được Git inventory; fingerprint `Template` 271, `FrontEndTemplate` 558, `SourceIntegrations` 14078 đều khớp |
| Git hygiene/security | PASS: 0 tracked-ignored, 0 generated dependency/build output, 0 source tham chiếu ngoài allowlist; Gitleaks 79 commit không có leak |
| Docker/runtime | PASS: Docker 29.6.2; app/nginx/queue/scheduler healthy; PHP 8.5.0, Laravel 13.23.0; 28 migration đã chạy; HTTP health/public API/Admin đạt |
| Backend | PASS: Composer platform requirements; Unit 23 test/358 assertion; PHPStan 540 file không lỗi |
| Admin/E2E inventory | PASS: lint; Vitest 30 file/63 test; production build/sync 123 file; Playwright nhận 15 test/4 file |
| Public assets | PASS: Vite production build và performance budget |

## Ranh giới và cảnh báo

- Snapshot chỉ nghiệm thu source tại base HEAD. Các thay đổi P51 chưa commit của owner được giữ nguyên, không stage và không dùng để nâng trạng thái P51.
- Pint trên working tree báo hai style issue trong `PublishScheduledPages.php` và `routes/web.php`, đều thuộc thay đổi P51 ngoài snapshot; base HEAD không bị sửa trong T012.
- Image runtime production cố ý cài Composer `--no-dev`, vì vậy không có `artisan test`/`vendor/bin/phpunit`. QA dùng source mount với dev dependencies hoặc CI QA image; không cài dev package vào runtime image.
- Quyền `hongvan` trên database test riêng `hongvan_testing` đã được khôi phục ở mức `<database>.*`; không migrate, reset hoặc thay đổi `hongvan_platform`.
