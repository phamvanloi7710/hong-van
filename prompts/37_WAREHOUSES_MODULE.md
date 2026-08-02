# PROMPT 37 — KHO BÃI VÀ YÊU CẦU THUÊ KHO

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS.

## Điều kiện tiên quyết

1. P31, P35 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P37 — Kho bãi và yêu cầu thuê kho
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P37.
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
Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS.

NHIỆM VỤ BẮT BUỘC:
1. Tạo warehouses/translations/media/facilities/services với address/map coordinates optional, area/capacity descriptive fields, security/PCCC descriptions, business hours.
2. Không tạo stock bins, inbound/outbound operations hoặc inventory ledger.
3. Admin CRUD kho, gallery, facilities, map, featured/status.
4. Public warehouse listing/detail SSR với map privacy/performance strategy.
5. Tạo Page Builder data sources/blocks.
6. Form warehouse request: goods, required area/volume, duration, start date, storage requirements, location, contact.
7. Policies/audit/cache/tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có WMS scope creep.
- [ ] Kho public hiển thị từ data thật.
- [ ] Map không hardcode key.
- [ ] Request context an toàn.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Warehouse`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P37.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 37 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P38.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P38.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có WMS scope creep.
- [ ] Kho public hiển thị từ data thật.
- [ ] Map không hardcode key.
- [ ] Request context an toàn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
