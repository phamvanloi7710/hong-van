# PROMPT 52 — BACKUP, MONITORING VÀ VẬN HÀNH

**Phase:** 08 — Operations  
**Flag:** `REQUIRED`

## Mục tiêu

Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố.

## Điều kiện tiên quyết

1. P51 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P52 — Backup, monitoring và vận hành
PHẠM VI: 08 — Operations
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P52.
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
Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế backup DB + media + env references, encryption, retention, offsite copy và access control.
2. Tạo backup scripts/jobs có lock, checksum và failure notification; không lưu plain secret.
3. Viết và test restore trên môi trường staging/temporary database.
4. Health endpoints tách liveness/readiness, không lộ config; kiểm tra DB/Redis/queue phù hợp.
5. Log structured request ID, queue failures, scheduler, security; rotation/retention.
6. Monitor disk, DB, Redis, queue backlog, HTTP errors, SSL expiry, backup age.
7. Tạo incident runbooks: app down, DB full, queue stuck, bad deploy, media missing, compromised account.
8. Admin health page restricted nếu triển khai.
9. Tạo `docs/reports/P52_RESTORE_TEST.md` bằng kết quả thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Backup chưa restore-test không được coi là hoàn tất.
- [ ] Health không lộ secret.
- [ ] Alert ownership/escalation documented.
- [ ] Retention rõ.

KIỂM TRA TỐI THIỂU:
- `run backup in staging`
- `restore to temporary DB/storage`
- `smoke restored app`
- `verify checksum`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P52.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 52 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P53.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P53.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Backup chưa restore-test không được coi là hoàn tất.
- [ ] Health không lộ secret.
- [ ] Alert ownership/escalation documented.
- [ ] Retention rõ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
