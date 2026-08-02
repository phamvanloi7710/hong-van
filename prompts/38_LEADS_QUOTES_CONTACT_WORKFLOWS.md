# PROMPT 38 — LEAD, BÁO GIÁ VÀ QUY TRÌNH TIẾP NHẬN

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái.

## Điều kiện tiên quyết

1. P25 form blocks, P33, P36, P37 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P38 — Lead, báo giá và quy trình tiếp nhận
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P38.
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
Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái.

NHIỆM VỤ BẮT BUỘC:
1. Tạo lead core, assignments, status histories, notes, contact/quote items và mapping transport/warehouse request.
2. Status allowlist: new, contacted, qualified/processing, done, spam, archived; transition policy rõ.
3. Public endpoints validate, anti-spam, rate limit, idempotency/dedup và consent.
4. Queue notifications email/database cho team theo settings; failure retry.
5. Admin inbox hợp nhất và views theo loại, filter, assignment, notes nội bộ, status timeline, export permission.
6. Nội dung gốc khách gửi immutable; nhân viên chỉ thêm note/status/assignment.
7. Redact dữ liệu nhạy cảm trong audit; retention/export/delete policy.
8. Dashboard metrics hooks.
9. Test duplicate, permission, transition, notification, immutable original.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Form blocks lưu lead thật.
- [ ] Không sửa nội dung gốc.
- [ ] Assignment/status history đầy đủ.
- [ ] Spam/rate limiting hoạt động.
- [ ] Notification queue không chặn request.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Lead`
- `cd BackEnd && php artisan test --filter=PublicSubmission`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P38.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 38 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P39.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P39.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Form blocks lưu lead thật.
- [ ] Không sửa nội dung gốc.
- [ ] Assignment/status history đầy đủ.
- [ ] Spam/rate limiting hoạt động.
- [ ] Notification queue không chặn request.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
