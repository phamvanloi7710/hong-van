# PROMPT 25 — BLOCK FORM VÀ CTA TẠO LEAD

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility.

## Điều kiện tiên quyết

1. P21–P23 DONE. Lead domain có thể dùng contract, hoàn thiện persistence ở P38.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P25 — Block form và CTA tạo lead
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P25.
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
Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa form block types cố định: contact, product quote, transport request, warehouse request.
2. Không cho Page Builder tạo arbitrary backend field/action; dùng field registry và form definition version.
3. Field schema: label, help, required, validation preset, consent, layout; không cho executable code.
4. Render Blade với CSRF, honeypot, accessible errors, success state.
5. Tạo public endpoint contracts, idempotency/dedup key và rate limit; persistence adapter có thể placeholder test double trước P38.
6. Product quote block tự bind product context khi trên product page.
7. Transport/warehouse fields theo charter.
8. Không gửi email trực tiếp trong request; queue notification.
9. Test spam, validation, duplicate, consent và tampering hidden context.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Arbitrary field/action bị reject.
- [ ] Form keyboard/screen-reader usable.
- [ ] Rate limit/honeypot hoạt động.
- [ ] Context product không thể bị giả mạo mà không validate.
- [ ] No synchronous slow email.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=FormBlock`
- `cd BackEnd && php artisan test --filter=PublicForm`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P25.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 25 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P26.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P26.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Arbitrary field/action bị reject.
- [ ] Form keyboard/screen-reader usable.
- [ ] Rate limit/honeypot hoạt động.
- [ ] Context product không thể bị giả mạo mà không validate.
- [ ] No synchronous slow email.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
