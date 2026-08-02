# PROMPT 08 — XÂY NỀN DATABASE VÀ CƯỠNG CHẾ TIỀN TỐ

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix.

## Điều kiện tiên quyết

1. P04 DONE.
2. MySQL test database sẵn sàng hoặc SQLite không được dùng để che khác biệt MySQL.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P08 — Xây nền database và cưỡng chế tiền tố
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P08.
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
Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix.

NHIỆM VỤ BẮT BUỘC:
1. Rà migration mặc định Laravel 13 và đổi tên mọi bảng: users, password reset, sessions, cache, jobs, failed jobs, batches, notifications, Sanctum, migrations registry khi cấu hình cho phép.
2. Không dùng connection-level prefix.
3. Tạo base contracts/traits cho `public_id`, audit stamps, sortable status nếu có lợi; không tạo abstraction chung chung.
4. Thiết lập database charset/collation hỗ trợ tiếng Việt và emoji phù hợp MySQL 8.4.
5. Chọn convention: internal bigint id + public ULID; ghi ADR.
6. Tạo `scripts/check-table-prefix.php` scan migrations, model table names và known config; exit non-zero khi phát hiện tên sai.
7. Tạo architecture test cho prefix.
8. Tạo migration foundation cho `hongvan_languages`, `hongvan_setting_groups`, `hongvan_settings` nếu thuộc core bắt buộc; chưa thêm business tables.
9. Kiểm tra migrate fresh, rollback batch và migrate lại.
10. Cập nhật DATABASE_BLUEPRINT bằng schema thực nếu khác.

KHÔNG ĐƯỢC:
- Không thêm bảng business ngoài scope.
- Không dùng DB_PREFIX connection config.

TIÊU CHÍ NGHIỆM THU:
- [ ] Database fresh chỉ có bảng `hongvan_*` do project tạo.
- [ ] Không double-prefix.
- [ ] Script prefix bắt được fixture sai trong test.
- [ ] Migrations rollback sạch.
- [ ] Model/core config trỏ đúng bảng.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --env=testing`
- `cd BackEnd && php artisan test --filter=TablePrefix`
- `cd BackEnd && php ../scripts/check-table-prefix.php`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P08.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 08 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P09.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P09.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Database fresh chỉ có bảng `hongvan_*` do project tạo.
- [ ] Không double-prefix.
- [ ] Script prefix bắt được fixture sai trong test.
- [ ] Migrations rollback sạch.
- [ ] Model/core config trỏ đúng bảng.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
