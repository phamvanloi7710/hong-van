# P47 — Seeder và dữ liệu mẫu an toàn

## Trạng thái

`PARTIAL`: phần backend hiện có đã hoàn tất. Demo page template/document chưa thể tạo vì Page Builder P18-P31 và block registry chưa tồn tại; frontend public được chủ dự án yêu cầu làm cuối cùng.

## Phạm vi hoàn tất

- `DatabaseSeeder` chỉ seed dữ liệu nền tảng an toàn: ngôn ngữ, permission/role, setting mặc định đã xác nhận, catalog mặc định và super admin tùy chọn từ environment.
- `SuperAdminSeeder` không ghi đè password hoặc trạng thái tài khoản đã tồn tại; chỉ gắn role `super_admin` và bổ sung preference mặc định còn thiếu.
- `DemoSeeder` phải được gọi tường minh và từ chối chạy khi application environment là `production`.
- Dữ liệu demo gồm media placeholder tự sinh tại local, category/sản phẩm, dịch vụ, cây trồng/giải pháp, loại xe/xe và kho.
- Tất cả nội dung hiển thị có bản dịch `vi`, `en`, `zh`, gắn nhãn `[DEMO]`, không seed MST, địa chỉ, hotline, chứng nhận, partner hoặc claim năng lực thật.
- `ProductFactory` có state `draft`, `published`, `archived`, `contactPrice`, `fixedPrice` và `rangePrice`.

## Database/API/UI

- Không thêm migration, bảng hoặc cột.
- Không thay đổi API.
- Không thay đổi Admin Angular hoặc public UI, vì vậy không cần build/sync Angular trong P47.

## Kiểm tra

- `php artisan migrate:fresh --seed --env=testing`: pass trên database được ép rõ là `hongvan_testing`.
- `php artisan db:seed --class=DemoSeeder --env=testing`: pass hai lần liên tiếp.
- Kiểm tra sau lần hai: 1 media placeholder, 3 sản phẩm, 1 service, 1 crop, 1 vehicle và 1 warehouse demo.
- `php artisan test --filter=Seeder`: 3 tests, 22 assertions, pass.
- `php artisan test`: 151 tests, 1247 assertions, pass.
- `composer analyse`: PHPStan/Larastan level 6, pass.
- `vendor\\bin\\pint --dirty`: pass.

## An toàn dữ liệu

Trước khi chạy `migrate:fresh`, cấu hình `--env=testing` ban đầu bị phát hiện đang fallback về database local của ứng dụng. Lệnh phá hủy đã được dừng trước khi thực thi. Sau đó toàn bộ biến kết nối được ép rõ sang `hongvan_testing`, xác minh tên database rồi mới reset/seed. Database local của ứng dụng không bị reset.

## Deferred

Demo page templates/documents sử dụng mọi block quan trọng và bước validate registry phải quay lại sau khi Page Builder P18-P31 cùng frontend template cuối cùng đã có. Không tạo schema JSON hoặc block giả để đánh dấu hoàn tất.
