# PROMPT 09 — XÂY NỀN API ADMIN V1

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module.

## Điều kiện tiên quyết

1. P08 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P09 — Xây nền API admin v1
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P09.
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
Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module.

NHIỆM VỤ BẮT BUỘC:
1. Tạo route namespace `/api/admin/v1` và public `/api/public/v1` tối thiểu.
2. Triển khai response envelope đúng `docs/API_CONVENTIONS.md` bằng Resource/response factory vừa đủ, không bọc file download/stream sai cách.
3. Tạo request ID middleware và log context, không lộ trace.
4. Chuẩn hóa validation exception, authorization exception, not found, conflict và rate-limit response.
5. Tạo pagination metadata.
6. Tạo typed filter/sort allowlist helpers; cấm client truyền raw column.
7. Thiết lập API locale từ user/request với allowlist.
8. Tạo `/api/admin/v1/system/ping` protected placeholder hoặc public health riêng theo security.
9. Viết feature tests cho success, validation, 404 và unexpected exception production behavior.
10. Tạo OpenAPI strategy document; chưa cần sinh toàn bộ spec.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Response contract nhất quán.
- [ ] Status code đúng.
- [ ] Production response không lộ stack trace.
- [ ] Sort injection bị từ chối.
- [ ] Request ID xuất hiện trong response/log context.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Api`
- `cd BackEnd && vendor/bin/pint --test`
- `cd BackEnd && vendor/bin/phpstan analyse app/Http app/Support`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P09.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 09 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P10.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P10.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Response contract nhất quán.
- [ ] Status code đúng.
- [ ] Production response không lộ stack trace.
- [ ] Sort injection bị từ chối.
- [ ] Request ID xuất hiện trong response/log context.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
