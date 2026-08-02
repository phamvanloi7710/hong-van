# PROMPT 48 — QA BACKEND TOÀN DIỆN

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks.

## Điều kiện tiên quyết

1. P47 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P48 — QA backend toàn diện
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P48.
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
Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks.

NHIỆM VỤ BẮT BUỘC:
1. Chạy full backend suite trên MySQL test.
2. Chạy Pint test, PHPStan/Larastan ở level đã chốt, composer audit.
3. Thêm architecture tests: table prefix, thin controllers threshold, no DB query in views, Page Builder no arbitrary renderer, no public draft.
4. Đo test coverage theo critical domains, không chạy theo % hình thức; bổ sung khoảng trống auth/RBAC/page publish/media/leads/pricing/SEO.
5. Test queue/scheduler idempotency.
6. Test migration fresh/rollback and route/config cache.
7. Phân loại lỗi existing/new; sửa root cause đúng scope.
8. Tạo `docs/reports/P48_BACKEND_QA.md` với lệnh và kết quả thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Full suite pass hoặc báo blocker cụ thể, không ghi DONE giả.
- [ ] Prefix/security critical tests pass.
- [ ] No pending migration.
- [ ] Composer audit được xử lý/ghi risk.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test`
- `cd BackEnd && vendor/bin/pint --test`
- `cd BackEnd && vendor/bin/phpstan analyse`
- `cd BackEnd && composer audit`
- `cd BackEnd && php ../scripts/check-table-prefix.php`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P48.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 48 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P49.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P49.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Full suite pass hoặc báo blocker cụ thể, không ghi DONE giả.
- [ ] Prefix/security critical tests pass.
- [ ] No pending migration.
- [ ] Composer audit được xử lý/ghi risk.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
