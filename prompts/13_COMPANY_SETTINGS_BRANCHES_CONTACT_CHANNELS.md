# PROMPT 13 — THIẾT LẬP THÔNG TIN CÔNG TY VÀ CẤU HÌNH TOÀN CỤC

**Phase:** 02 — Core CMS  
**Flag:** `REQUIRED`

## Mục tiêu

Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp.

## Điều kiện tiên quyết

1. P11 DONE.
2. Core settings tables P08 tồn tại.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P13 — Thiết lập thông tin công ty và cấu hình toàn cục
PHẠM VI: 02 — Core CMS
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P13.
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
Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế setting groups: company, legal, contact, social, branding, business hours, map, quote, email, SEO defaults, feature flags.
2. Tạo branches, business hours, social links, contact channels với order/status.
3. Secret settings phải dùng encrypted storage hoặc env reference; không trả plain secret về Angular.
4. Tạo typed settings service và cache với invalidation.
5. Tạo admin forms theo group, validation và permission `settings.*`.
6. Logo/favicon/OG default chọn từ Media contract, có thể tạm null trước P16.
7. Không seed địa chỉ, MST, hotline giả; chỉ seed tên pháp lý và locale/timezone khi đã xác nhận.
8. Tạo public helper/view model để Blade dùng settings mà không query lặp.
9. Audit changes và redaction.
10. Test cache invalidation, permission và secret masking.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Thông tin công ty chỉnh được từ admin.
- [ ] Không hardcode contact trong Blade/Angular.
- [ ] Secret không lộ qua API/log.
- [ ] Cache cập nhật ngay sau save.
- [ ] Data chưa có để trống có validation phù hợp.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Settings`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P13.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 13 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P14.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P14.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Thông tin công ty chỉnh được từ admin.
- [ ] Không hardcode contact trong Blade/Angular.
- [ ] Secret không lộ qua API/log.
- [ ] Cache cập nhật ngay sau save.
- [ ] Data chưa có để trống có validation phù hợp.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
