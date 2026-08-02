# Local development

## Mục tiêu môi trường

Backend Laravel đã được bootstrap ở P04. Các phiên bản mục tiêu đã chốt:

| Công cụ | Phiên bản mục tiêu |
| --- | --- |
| PHP | `8.5.x` |
| Composer | `2.x` |
| Node.js | `24.15.x` LTS |
| npm | đi kèm Node.js mục tiêu |
| Git | bản còn được hỗ trợ |

Môi trường Windows/WAMP đã được nâng cấp và chọn PHP `8.5.9` ở P04. PHP `8.4.1` vẫn được giữ lại để có thể chuyển phiên bản khi cần.

## Chuẩn bị trên Windows

1. Clone repository vào một đường dẫn không yêu cầu quyền Administrator.
2. Bảo đảm `php`, `composer`, `node`, `npm` và `git` có trong `PATH`.
3. Từ thư mục gốc repository, chạy:

   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\verify-prerequisites.ps1
   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\verify-readonly-sources.ps1
   ```

4. Tạo `BackEnd/.env` từ `BackEnd/.env.example`, sau đó chạy `php artisan key:generate`. Không commit `.env` hoặc secret.

VirtualHost WAMP `hongvan.local` trỏ tới `D:/www/HongVan/BackEnd/public`. Có thể kiểm tra runtime tại `http://hongvan.local/health`.

## Chuẩn bị trên Linux

1. Clone repository bằng user thường.
2. Bảo đảm `php`, `composer`, `node`, `npm`, `git`, `bash` và `sha256sum` có trong `PATH`.
3. Từ thư mục gốc repository, chạy:

   ```bash
   bash ./scripts/verify-prerequisites.sh
   bash ./scripts/verify-readonly-sources.sh
   ```

4. Cấu hình web server chỉ sau khi backend được bootstrap; document root phải là `BackEnd/public`.

## Nguồn tham chiếu chỉ đọc

Ba vùng sau không phải source đích và không được sửa trong quá trình triển khai thông thường:

- `Template/`
- `FrontEndTemplate/`
- `SourceIntegrations/`

File `.readonly-sources.sha256` ghi dấu vân tay đã được duyệt. Chạy script verify trước và sau một prompt có đọc nguồn tham chiếu. Khi chủ dự án chủ động thay thế nguồn, cần audit lại nguồn mới trước; sau đó mới tạo nội dung baseline mới bằng `-PrintBaseline` hoặc `--print-baseline` và cập nhật file baseline có chủ đích.

## Database MySQL cho test

Test backend dùng MySQL thật, không dùng SQLite in-memory. Tạo riêng database test với đúng charset/collation và không trỏ test vào database local chứa dữ liệu:

```sql
CREATE DATABASE hongvan_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;
```

`BackEnd/phpunit.xml` mặc định kết nối `127.0.0.1:3306`, database `hongvan_testing`, user `root` không password cho WAMP local. Nếu môi trường khác, đặt `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong process chạy test; không commit credential.

Các lệnh kiểm chứng P08:

```powershell
php artisan migrate:fresh --env=testing --force
php artisan migrate:rollback --env=testing --force
php artisan migrate --env=testing --force
php artisan test --filter=TablePrefix
php ..\scripts\check-table-prefix.php
```

Checker phải fail khi migration, foreign key literal, model `$table` hoặc cấu hình framework tham chiếu bảng không bắt đầu bằng `hongvan_`.

## Lệnh backend

```powershell
cd .\BackEnd
composer install
php artisan serve
php artisan test
php vendor/bin/pint --test
composer analyse
```

Không chạy migration/seed cho đến khi prompt database tương ứng đã tạo migration có prefix `hongvan_`.

## Lệnh Angular Admin

```powershell
cd .\Admin
npm ci
npm run lint
npm test -- --watch=false
npm run build
npm run build:laravel
```

Angular 22.1 tạo browser bundle thực tế tại `Admin/dist/hongvan-admin/browser/`. `npm run build:laravel` build production, kiểm tra base href `/admin/`, asset tham chiếu và source-map policy, sau đó xóa có guard output cũ rồi đồng bộ vào `BackEnd/public/admin/browser/`. Không sửa thủ công bundle đã sinh; thư mục đích được Git ignore.

Từ repository root, pipeline đa nền tảng mặc định chạy lint, test, build và sync. `npm ci` chỉ chạy khi `node_modules` chưa có hoặc lock snapshot cũ hơn `package-lock.json`:

```powershell
.\scripts\build-admin.ps1
.\scripts\build-admin.ps1 -Mode BuildOnly -SkipInstall
```

```bash
bash ./scripts/build-admin.sh
bash ./scripts/build-admin.sh --build-only --skip-install
```

## Phục vụ Angular Admin qua Laravel

- Entry point và mọi client-side deep link dưới `/admin/` dùng route tên `admin.spa` đặt sau public routes.
- `/api/*`, `/preview/*` và public routes không đi qua admin catch-all.
- `index.html` trả `private, no-store, no-cache`; asset có hash trả `public, max-age=31536000, immutable`.
- Asset bị thiếu trả 404 thay vì trả nhầm HTML SPA.
- WAMP dùng `BackEnd/public/admin/.htaccess` để chuyển `/admin/*` vào Laravel; output Angular vẫn nằm trong `browser/`.

Với Nginx, document root vẫn là `BackEnd/public`. Cấu hình `/admin/` đi qua Laravel front controller để Laravel áp dụng cùng fallback và cache policy:

```nginx
location /admin/ {
    try_files __hongvan_admin_spa__ /index.php?$query_string;
}
```

Production build đặt `sourceMap: false`. Có thể smoke test local tại:

```text
http://hongvan.local/admin/
http://hongvan.local/admin/dashboard
```

Nếu terminal Windows vẫn trả `PHP 8.4.1` từ `PATH`, dùng đúng binary `C:\wamp64\bin\php\php8.5.9\php.exe` cho Composer/Artisan của project hoặc cập nhật `PATH` của terminal trước khi chạy lệnh backend.

## Nguyên tắc an toàn

- Không commit `.env`, log, dependency hoặc build output.
- Không chạy migration hay SQL phá hủy nếu chưa xác nhận đúng môi trường và database.
- Không sửa trực tiếp ba vùng nguồn tham chiếu.
- Không tắt CSRF, CORS hoặc middleware bảo mật để xử lý lỗi local.
