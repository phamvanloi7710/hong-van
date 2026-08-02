# PROMPT 19 — PORT FRONTEND TEMPLATE PUBLIC VÀO LARAVEL BLADE

**Phase:** 03 — Media & Frontend  
**Flag:** `DEFERRED_ALLOWED`

## Mục tiêu

Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder.

## Điều kiện tiên quyết

1. P18 DONE.
2. Gate FrontEndTemplate = READY. Nếu source thiếu: DEFERRED và dừng prompt này.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P19 — Port frontend template public vào Blade
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: DEFERRED_ALLOWED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P19.
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
Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder.

NHIỆM VỤ BẮT BUỘC:
1. Đọc inventory/source `FrontEndTemplate/`, xác định layouts/pages/sections/assets/plugins/license.
2. Tách typography, colors, spacing, containers, breakpoints, buttons, forms, cards thành token/component; tránh copy CSS không kiểm soát.
3. Port header/footer/navigation, home sections, listing/detail templates, contact và content layouts sang Blade.
4. Thay asset/path hardcode bằng Vite/Media/settings helpers.
5. Loại bỏ plugin JS không cần; thay bằng giải pháp nhẹ, accessible khi có thể.
6. Không thay đổi source tham chiếu.
7. Tạo mapping `template section → Page Builder block type` trong docs.
8. Visual compare desktop/tablet/mobile và sửa chênh lệch có chủ đích.
9. Giữ nội dung demo ngoài seed, không hardcode vào view.

KHÔNG ĐƯỢC:
- Không chạy nguyên template tĩnh trong production.
- Không sửa source tham chiếu.

TIÊU CHÍ NGHIỆM THU:
- [ ] Source FrontEndTemplate diff = 0.
- [ ] Blade output đạt visual fidelity.
- [ ] Core content vẫn SSR.
- [ ] Không có broken asset/external demo link.
- [ ] Design tokens rõ và dùng chung với block.

KIỂM TRA TỐI THIỂU:
- `git diff -- FrontEndTemplate`
- `cd BackEnd && npm run build`
- `cd BackEnd && php artisan test --filter=PublicFrontend`
- `visual regression command nếu đã có`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P19.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 19 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P20.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P20.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Source FrontEndTemplate diff = 0.
- [ ] Blade output đạt visual fidelity.
- [ ] Core content vẫn SSR.
- [ ] Không có broken asset/external demo link.
- [ ] Design tokens rõ và dùng chung với block.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
