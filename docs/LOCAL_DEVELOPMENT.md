# Local development

## Mục tiêu môi trường

Repository hiện mới ở giai đoạn chuẩn bị. P03 không cài Laravel, Angular hoặc package. Các phiên bản mục tiêu đã chốt:

| Công cụ | Phiên bản mục tiêu |
| --- | --- |
| PHP | `8.5.x` |
| Composer | `2.x` |
| Node.js | `24.15.x` LTS |
| npm | đi kèm Node.js mục tiêu |
| Git | bản còn được hỗ trợ |

Máy Windows được kiểm tra ở P00 đang có PHP `8.4.1`, vì vậy script prerequisite sẽ báo không tương thích cho đến khi PHP `8.5.x` được chọn. Đây là cảnh báo môi trường, không phải chỉ dẫn tự nâng cấp.

## Chuẩn bị trên Windows

1. Clone repository vào một đường dẫn không yêu cầu quyền Administrator.
2. Bảo đảm `php`, `composer`, `node`, `npm` và `git` có trong `PATH`.
3. Từ thư mục gốc repository, chạy:

   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\verify-prerequisites.ps1
   powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\verify-readonly-sources.ps1
   ```

4. Chỉ tạo `BackEnd/.env` từ file example của Laravel sau khi P04 đã bootstrap backend. Không đặt secret vào `.env.example` ở root.

Tên miền local dự kiến là `hongvan.local`. Việc xóa và tạo lại VirtualHost WAMP được hoãn đến prompt sở hữu cấu hình runtime, sau khi `BackEnd/public` tồn tại; không trỏ domain vào repository root.

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

## Lệnh ứng dụng

Lệnh cài dependency, migrate, seed, test và build sẽ được bổ sung khi Laravel/Angular thực sự được bootstrap. Không giả định cấu trúc hoặc script chưa tồn tại.

## Nguyên tắc an toàn

- Không commit `.env`, log, dependency hoặc build output.
- Không chạy migration hay SQL phá hủy nếu chưa xác nhận đúng môi trường và database.
- Không sửa trực tiếp ba vùng nguồn tham chiếu.
- Không tắt CSRF, CORS hoặc middleware bảo mật để xử lý lỗi local.
