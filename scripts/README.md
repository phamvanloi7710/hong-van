# Scripts

Các script hỗ trợ Windows PowerShell và Bash:

- `verify-prerequisites.*`: kiểm tra PHP, Composer, Node.js, npm và Git; không thay đổi môi trường.
- `verify-readonly-sources.*`: đối chiếu dấu vân tay của `Template/`, `FrontEndTemplate/` và `SourceIntegrations/` với `.readonly-sources.sha256`.
- `qa-backend.*`: Composer validate/audit, kiểm tra prefix, Pint, Larastan và toàn bộ Laravel test. Tùy chọn migration chỉ chạy khi `APP_ENV=testing|ci` và tên database kết thúc bằng `_testing` hoặc `_ci`.
- `qa-admin.*`: chỉ cài từ lockfile bằng `npm ci`, audit dependency, lint, unit test, build/sync Laravel và tùy chọn Playwright E2E.
- `build-admin.*`: build Angular production, kiểm tra base href/source map/asset rồi đồng bộ có guard vào `BackEnd/public/admin/browser`.
- `smoke.*`: kiểm tra `/health`, public ping, Admin SPA và trạng thái 401 của API Admin khi chưa đăng nhập.
- `create-build-artifact.*`: build public/admin, gom output vào `.tmp/artifacts/hongvan-web-assets` và tạo `SHA256SUMS.txt`; output này bị Git ignore.
- `verify.*`: chạy chuỗi kiểm tra local; có tùy chọn install, migration test, E2E và smoke URL.
- `serve-spa.mjs`: HTTP server không dependency chỉ dùng trong optional CI E2E để phục vụ production Admin bundle tại `/admin/`.

Lệnh chuẩn trên Windows:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/verify.ps1 -SkipInstall -RunE2E -SmokeBaseUrl http://hongvan.local
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/create-build-artifact.ps1 -SkipInstall
```

Lệnh tương đương trên Linux/macOS/Git Bash:

```bash
bash scripts/verify.sh --skip-install --run-e2e --smoke-base-url http://hongvan.local
bash scripts/create-build-artifact.sh --skip-install
```

Không truyền tùy chọn migration vào database local/production. Script có guard nhưng người chạy vẫn phải kiểm tra chính xác `APP_ENV` và `DB_DATABASE` trước khi chạy.

Kiểm tra fingerprint source tham chiếu mặc định luôn bật. Chỉ dùng `-SkipReadonlySourceCheck` hoặc `--skip-readonly-source-check` khi chủ dự án đang chủ động thay source ignored và không muốn cập nhật fingerprint trong prompt hiện tại; tùy chọn này không sửa/xóa source.
