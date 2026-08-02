# PROMPT 55 — CUTOVER PRODUCTION

**Phase:** 09 — Launch  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai production theo checklist có backup, rollback và verification cụ thể.

## Điều kiện tiên quyết

1. P54 UAT approved.
2. P53 no unresolved critical/high.
3. Domain/TLS/production env sẵn sàng.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P55 — Cutover production
PHẠM VI: 09 — Launch
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P55.
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
Triển khai production theo checklist có backup, rollback và verification cụ thể.

NHIỆM VỤ BẮT BUỘC:
1. Chốt release tag/commit, lockfiles, changelog và artifact checksum.
2. Backup production hiện tại nếu có; verify backup.
3. Build từ checkout sạch qua CI.
4. Deploy code/artifact, set env/secrets, migrate `--force`, cache, queue/scheduler, storage.
5. Thực hiện maintenance/minimal downtime plan.
6. Smoke public home/product/service/transport/warehouse/contact, admin login, media, page preview, lead submit, email/notification, sitemap/robots.
7. Kiểm tra HTTPS, headers, debug off, logs, queue, scheduler, metrics.
8. Nếu gate fail, rollback theo runbook, không vá tay không ghi nhận.
9. Tạo `docs/reports/P55_PRODUCTION_CUTOVER.md` với timestamp, version, checks và kết quả.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Production healthy.
- [ ] No debug/secret leakage.
- [ ] DB schema đúng prefix.
- [ ] Admin/public deep links hoạt động.
- [ ] Rollback đã sẵn sàng.

KIỂM TRA TỐI THIỂU:
- `production smoke commands`
- `php artisan about --only=environment`
- `queue/scheduler health`
- `TLS/security headers check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P55.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 55 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P56.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P56.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Production healthy.
- [ ] No debug/secret leakage.
- [ ] DB schema đúng prefix.
- [ ] Admin/public deep links hoạt động.
- [ ] Rollback đã sẵn sàng.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
