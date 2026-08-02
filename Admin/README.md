# HongVan Admin

Angular standalone SPA dành cho khu vực quản trị của HongVan.

## Yêu cầu

- Node.js `^22.22.3`, `^24.15.0` hoặc `>=26.0.0`.
- npm `>=11.12.1 <12`.

## Cài đặt và chạy

```powershell
npm ci
npm start
```

Ứng dụng dùng base path `/admin/` và API base `/api/admin/v1`; không hardcode domain.

Các route nền tảng hiện có:

- `/admin/dashboard`: Admin shell responsive, menu dọc/ngang và theme settings local.
- `/admin/login`: Auth shell và form UI; chưa kết nối authentication API.

## Kiểm tra

```powershell
npm run lint
npm test -- --watch=false
npm run build
npm run build:laravel
```

`npm run build:laravel` tạo production build, kiểm tra `<base href="/admin/">`, asset tham chiếu và policy không source map, sau đó đồng bộ có guard vào `BackEnd/public/admin/browser/`.

Có thể chạy toàn bộ lint, test, build và sync từ repository root:

```powershell
.\scripts\build-admin.ps1 -SkipInstall
```

```bash
bash ./scripts/build-admin.sh --skip-install
```
