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

## Nguyên tắc an toàn

- Không commit `.env`, log, dependency hoặc build output.
- Không chạy migration hay SQL phá hủy nếu chưa xác nhận đúng môi trường và database.
- Không sửa trực tiếp ba vùng nguồn tham chiếu.
- Không tắt CSRF, CORS hoặc middleware bảo mật để xử lý lỗi local.
