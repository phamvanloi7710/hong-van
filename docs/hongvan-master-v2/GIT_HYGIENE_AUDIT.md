# T009 Git Hygiene Audit

Audit tại base HEAD `57596ab731a5f772b94d1a2e9657ae5ea46ebfdd` ngày 2026-08-09.

## Kết quả

- `git ls-files -ci --exclude-standard`: 0 file tracked nhưng bị ignore.
- Dependency và build output: 0 file tracked trong `vendor`, `node_modules`, `dist`, `public/build` hoặc `public/admin/browser`.
- Cache, log và upload: chỉ có 7 file `.gitignore` giữ cấu trúc thư mục; không có dữ liệu runtime được track.
- Source tham chiếu: 7 file hướng dẫn được allowlist; không có asset/source có giấy phép được track.
- File lớn hơn 5 MiB tại HEAD: 0.
- Probe ignore đạt cho Angular dist/cache/coverage, Laravel build/cache/log/upload, Playwright output, `.env` và ba source tham chiếu.
- Gitleaks `v8.28.0` dùng đúng lệnh CI đã quét 76 commit, khoảng 6,65 MB và báo `no leaks found`.
- `.gitleaksignore` chỉ chứa một fingerprint chính xác cho fake Stripe key lịch sử đã được tài liệu hóa; không có ignore rộng.
- Không rewrite/xóa lịch sử, không force push và không stage thay đổi P51 đang dở.

## Lệnh bằng chứng

```text
git ls-files -ci --exclude-standard
git ls-files Template FrontEndTemplate SourceIntegrations
git check-ignore -v --no-index <probe>
git ls-tree -r -l HEAD
docker run --rm -v <repo>:/repo -w /repo zricethezav/gitleaks:v8.28.0 git --gitleaks-ignore-path .gitleaksignore --redact --no-banner .
```
