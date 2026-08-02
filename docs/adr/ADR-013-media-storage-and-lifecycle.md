# ADR-013: Media storage và lifecycle độc lập URL

- Status: Accepted
- Date: 2026-08-03

## Bối cảnh

Media sẽ được nhiều domain dùng chung và có thể chuyển từ local disk sang S3-compatible storage. Hệ thống cần xác thực upload từ nội dung thật, tạo nhiều variant bất đồng bộ, biết file đang được dùng ở đâu và không gắn dữ liệu nghiệp vụ với một public URL cố định.

## Quyết định

- Bản ghi Media và variant lưu `disk`, `path` do server sinh, metadata, checksum, kích thước, MIME và trạng thái. URL truy cập được sinh ở lớp API theo `public_id` và quyền hiện hành.
- `MediaStorage` là abstraction duy nhất cho đọc/ghi/xóa qua Laravel Filesystem; local và S3-compatible dùng cùng contract.
- `MediaUploadInspector` kiểm tra MIME thực, extension allowlist, giới hạn kích thước, executable/script prefix và khả năng decode ảnh. SVG bị chặn mặc định.
- Thumbnail, WebP và AVIF được tạo bởi queued job. `hongvan_media_operations` giữ trạng thái, số lần thử và lỗi đã làm sạch để hỗ trợ retry/cleanup.
- `hongvan_media_usages` lưu owner contract nằm trong allowlist. Media còn usage không được trash hoặc xóa vĩnh viễn.
- Lifecycle metadata, move, trash, restore, retry và permanent delete đi qua service, Policy và `AuditTrail`.
- Angular dùng `MediaPickerContract` có kiểu dữ liệu rõ ràng. P16 chỉ cung cấp UI nền tảng trung tính; việc clone StayHub thuộc P17 và chỉ thực hiện khi source tham chiếu tồn tại.

## Hệ quả

- Domain mới phải đăng ký/gỡ usage bằng `MediaUsageTracker`; không tự lưu URL hoặc đường dẫn filesystem làm nguồn chân lý.
- Queue worker cho queue `media` là thành phần bắt buộc trong runtime có upload ảnh.
- Production phải cấu hình disk, visibility, quota/retention và credentials qua environment; không hardcode S3 key trong code.
- Xóa vật lý là tác vụ có kiểm soát. Cleanup chỉ xử lý media đã trash quá retention và vẫn không có usage.
