# PROMPT 12 — LƯU THEME ADMIN THEO TỪNG USER

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn.

## Điều kiện tiên quyết

1. P06, P10 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P12 — Lưu theme admin theo từng user
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P12.
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
Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_user_preferences` với namespace/key hoặc typed columns phù hợp; có unique user+namespace.
2. Định nghĩa schema allowlist cho mode, skin, menu style, compact, direction, density, allowed color tokens và các option thật sự có trong template.
3. Tạo API get/update/reset preference; validation server-side.
4. Angular bootstrap theme trước khi paint nếu có thể để giảm flash, nhưng không chặn app vô hạn.
5. Cache local chỉ là optimization; server là nguồn chân lý.
6. Merge order: template default → system default → user preference.
7. Handle preference cũ/invalid khi template update.
8. Tạo UI theme panel đúng template, nút reset và preview.
9. Audit thay đổi quan trọng nếu ảnh hưởng accessibility.
10. Test hai user có theme khác nhau và không rò preference.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Theme tồn tại qua logout/login và thiết bị khác.
- [ ] User A không đọc/sửa user B.
- [ ] Invalid token bị reject.
- [ ] Fallback hoạt động khi preference lỗi.
- [ ] Build pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=UserPreference`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P12.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 12 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P13.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P13.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Theme tồn tại qua logout/login và thiết bị khác.
- [ ] User A không đọc/sửa user B.
- [ ] Invalid token bị reject.
- [ ] Fallback hoạt động khi preference lỗi.
- [ ] Build pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
