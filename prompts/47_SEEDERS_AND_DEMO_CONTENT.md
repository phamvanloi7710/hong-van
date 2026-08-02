# PROMPT 47 — SEEDER VÀ DỮ LIỆU MẪU AN TOÀN

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật.

## Điều kiện tiên quyết

1. P32–P46 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P47 — Seeder và dữ liệu mẫu an toàn
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P47.
6. Không tự chạy prompt tiếp theo.

BỐI CẢNH BẤT BIẾN:
- Backend Laravel 13.x, PHP 8.5.x.
- Admin Angular 22.1.x, source tại Admin/, template tham chiếu read-only tại Template/.
- Public frontend dùng Laravel Blade; template tham chiếu read-only tại FrontEndTemplate/.
- Media source StayHub read-only tại SourceIntegrations/StayHubMedia/.
- Tất cả bảng project phải ghi rõ prefix hongvan_; cấm connection-level prefix.
- Catalog sản phẩm chỉ giới thiệu/nhận báo giá; cấm tự thêm cart, checkout, payment.
- Page Builder chỉ lưu JSON có schema; cấm thực thi Blade/PHP/JS tùy ý từ database.
- Preview Page Builder phải dùng cùng Blade renderer/CSS của public frontend.
- Không bịa thông tin pháp lý, địa chỉ, hotline, chứng nhận, đối tác hoặc năng lực công ty.
- Ưu tiên thay đổi nhỏ, đúng phạm vi, có test; không refactor tiện tay.
- Khi sửa function/method hiện hữu, phải đọc và giữ đầy đủ function/method, không dùng pseudo-code hoặc đoạn rút gọn.

MỤC TIÊU:
Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật.

NHIỆM VỤ BẮT BUỘC:
1. Tạo idempotent seeders: permissions/roles, super admin từ env, languages, settings defaults, theme, page templates, product categories, demo products, services, crops, warehouses/vehicles demo có nhãn DEMO.
2. Không seed MST, địa chỉ, hotline, chứng nhận, partner logo hoặc claim năng lực như dữ liệu thật.
3. Tạo demo page documents sử dụng mọi block quan trọng.
4. Media fixture dùng local generated placeholder hợp pháp, không hotlink.
5. Tách `DatabaseSeeder` production-safe và `DemoSeeder` explicit.
6. Factory states draft/published/archived/contact price/fixed/range.
7. Test migrate fresh + seed và repeat seed không duplicate.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Production seeder không tạo fake business claims.
- [ ] Demo seeder rõ nhãn.
- [ ] Không duplicate.
- [ ] Page demo validate registry.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --seed --env=testing`
- `cd BackEnd && php artisan db:seed --class=DemoSeeder --env=testing`
- `run DemoSeeder lần 2`
- `cd BackEnd && php artisan test --filter=Seeder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P47.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 47 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P48.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P48.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Production seeder không tạo fake business claims.
- [ ] Demo seeder rõ nhãn.
- [ ] Không duplicate.
- [ ] Page demo validate registry.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
