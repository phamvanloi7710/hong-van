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

## Kiểm tra

```powershell
npm run lint
npm test -- --watch=false
npm run build
```

`npm run build:laravel` là placeholder fail-fast dành cho P07. Chưa có bundle nào được đồng bộ vào Laravel ở P05.
