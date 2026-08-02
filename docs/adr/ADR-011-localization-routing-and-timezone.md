# ADR-011: Locale public, fallback, translation tables và timezone

- Status: Accepted
- Date: 2026-08-03

## Bối cảnh

Nội dung CMS về sau phải hỗ trợ `vi`, `en`, `zh`, cho phép tắt một ngôn ngữ public mà không làm hỏng route, đồng thời cần query/report bản dịch và slug theo locale. Database phải lưu thời gian UTC trong khi giao diện quản trị hiển thị theo giờ Việt Nam.

## Quyết định

- `hongvan_languages` là nguồn chân lý runtime cho trạng thái active, default và fallback; `vi` là mặc định, còn `en`/`zh` fallback về `vi`.
- URL public tiếng Việt mặc định không có prefix. Locale phụ dùng prefix đầu đường dẫn (`/en`, `/zh`); middleware public chỉ chấp nhận locale đã đăng ký và chuyển locale bị tắt về route mặc định.
- Core Admin tiếp tục dùng catalog build-time typed `vi/en/zh`; nội dung có nhu cầu query/report dùng `hongvan_translation_keys` và `hongvan_translation_values`, không gom vào một JSON đa ngôn ngữ.
- Entity nội dung dùng translation table riêng và contract `TranslatableEntity`/`HasTranslations`. Mỗi translation table phải unique theo entity + locale.
- `hongvan_localized_slugs` giữ uniqueness theo `language_id + namespace + slug`; entity sở hữu dùng định danh ổn định, không nhận tên bảng tùy ý từ request.
- Resolver đọc theo chuỗi fallback và trả khóa/default an toàn khi vẫn thiếu. Public request không tự tạo translation key/value.
- `APP_TIMEZONE=UTC`, MySQL session `DB_TIMEZONE=+00:00`; API trả ISO 8601 UTC. Admin hiển thị `Asia/Ho_Chi_Minh`, trừ khi sau này có user preference được duyệt riêng.

## Hệ quả

- Ngôn ngữ có thể chuẩn bị nội dung trước khi bật public.
- Báo cáo thiếu bản dịch có thể chạy bằng query chuẩn mà không phân tích JSON.
- Mỗi domain ở prompt sau vẫn phải tạo bảng translation có comment, foreign key và unique constraint riêng; registry slug không thay thế constraint của bảng domain.
- Public frontend đầy đủ vẫn chờ template theo quyết định của chủ dự án; P14 chỉ đặt middleware và route foundation.
