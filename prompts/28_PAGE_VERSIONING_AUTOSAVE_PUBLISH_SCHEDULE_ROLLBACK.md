# PROMPT 28 — VERSIONING VÀ XUẤT BẢN PAGE

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation.

## Điều kiện tiên quyết

1. P27 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P28 — Versioning và xuất bản page
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P28.
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
Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa draft working state và milestone versions; tránh tạo version mỗi keystroke.
2. Save tạo immutable page version với author, note, checksum, schema version.
3. Publish now cập nhật published version trong transaction, audit, invalidate cache, sitemap signal.
4. Schedule publish có timezone input nhưng lưu UTC, xử lý bằng scheduler/queue idempotent.
5. Rollback tạo version mới từ version cũ và publish theo explicit action.
6. Angular UI history compare metadata, preview version, publish dialog, schedule, rollback confirmation.
7. Optimistic concurrency bằng version/checksum; conflict trả 409 và UI xử lý reload/compare.
8. Không cho publish document invalid, missing required SEO/title/translation theo policy.
9. Test race, duplicate scheduler run, rollback và permission.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Published version không bị update.
- [ ] Concurrent edit không silent overwrite.
- [ ] Schedule đúng timezone.
- [ ] Cache invalidated.
- [ ] Audit đầy đủ.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PagePublishing`
- `cd BackEnd && php artisan schedule:test hoặc test scheduler phù hợp`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P28.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 28 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P29.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P29.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Published version không bị update.
- [ ] Concurrent edit không silent overwrite.
- [ ] Schedule đúng timezone.
- [ ] Cache invalidated.
- [ ] Audit đầy đủ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
