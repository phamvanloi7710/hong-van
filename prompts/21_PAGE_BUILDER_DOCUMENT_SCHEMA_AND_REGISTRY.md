# PROMPT 21 — PAGE BUILDER SCHEMA VÀ BLOCK REGISTRY

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Xây lõi document, block registry, validation, migrations và API metadata phía server.

## Điều kiện tiên quyết

1. P18, P20 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P21 — Page Builder schema và block registry
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P21.
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
Xây lõi document, block registry, validation, migrations và API metadata phía server.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/models pages, translations, versions, schedules, locks, templates, preview sessions theo blueprint.
2. Định nghĩa PageDocument schema version 1 với block id/type/version/props/style/visibility/bindings/children.
3. Tạo server BlockRegistry; mỗi block khai báo type, version, schema, defaults, parent/children, renderer, sanitizer, data dependencies.
4. Tạo validator có path-specific errors, limit payload/depth/block count và cycle detection.
5. Tạo block version migrator; import document cũ phải migrate tuần tự.
6. Tạo API registry metadata cho Angular, không lộ internal class/path.
7. Tạo Page CRUD metadata/draft shell, chưa cần UI builder đầy đủ.
8. Tạo cache key/tag contract.
9. Tạo tests invalid type, arbitrary view, script payload, too deep, duplicate id, invalid child.
10. Cập nhật PAGE_BUILDER_CONTRACT theo code thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Database không lưu Blade/PHP.
- [ ] Unknown block bị reject.
- [ ] Published version model immutable contract.
- [ ] Registry API typed.
- [ ] Security tests pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PageBuilder`
- `cd BackEnd && php artisan test --filter=PageDocument`
- `cd BackEnd && vendor/bin/phpstan analyse app/Domain/PageBuilder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P21.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 21 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P22.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P22.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Database không lưu Blade/PHP.
- [ ] Unknown block bị reject.
- [ ] Published version model immutable contract.
- [ ] Registry API typed.
- [ ] Security tests pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
