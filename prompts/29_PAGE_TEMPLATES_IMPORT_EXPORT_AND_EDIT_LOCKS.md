# PROMPT 29 — PAGE TEMPLATES, IMPORT/EXPORT VÀ EDIT LOCKS

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật.

## Điều kiện tiên quyết

1. P28 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P29 — Page templates, import/export và edit locks
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P29.
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
Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật.

NHIỆM VỤ BẮT BUỘC:
1. Tạo page templates/version, categories và permissions.
2. Cho save page as template, create page from template, duplicate page với reset slug/status.
3. Import/export JSON kèm manifest schema/theme/block versions; không chứa secret/private media URLs.
4. Import qua schema migration, block registry validation, media reference mapping và size limits.
5. Tạo edit lock/heartbeat với TTL, owner, force unlock permission và audit.
6. UI template library, export/download, import validation report, lock banner.
7. Không dùng lock vĩnh viễn; recover sau crash.
8. Test malicious import, old schema migration, missing block/media, concurrent locks.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Import không thực thi code.
- [ ] Template/version immutable hợp lý.
- [ ] Lock không làm mất nội dung.
- [ ] Force unlock restricted.
- [ ] Export có thể round-trip.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PageTemplate`
- `cd BackEnd && php artisan test --filter=PageImport`
- `cd Admin && npm test -- --watch=false`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P29.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 29 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P30.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P30.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Import không thực thi code.
- [ ] Template/version immutable hợp lý.
- [ ] Lock không làm mất nội dung.
- [ ] Force unlock restricted.
- [ ] Export có thể round-trip.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
