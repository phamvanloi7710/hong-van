# PROMPT 46 — ACCESSIBILITY, RESPONSIVE VÀ PERFORMANCE

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý.

## Điều kiện tiên quyết

1. Core modules/page builder DONE; FrontEndTemplate có thể đã port hoặc neutral theme được chấp nhận.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P46 — Accessibility, responsive và performance
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P46.
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
Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý.

NHIỆM VỤ BẮT BUỘC:
1. Audit semantic headings, landmarks, labels, focus, keyboard, contrast, reduced motion, alt/decorative, dialogs, tables, errors.
2. Sửa public header/menu/forms/gallery/page blocks và admin critical workflows.
3. Responsive test breakpoints template: mobile/tablet/desktop; no horizontal overflow.
4. Tối ưu image variants, sizes/srcset, lazy/eager hero, dimensions, font loading, JS plugins.
5. Cache published pages/data, Vite chunking/admin lazy routes, remove unused heavy dependencies.
6. Đo baseline Lighthouse/Web Vitals trên representative pages; ghi môi trường và không làm số liệu giả.
7. Set performance budgets cho public CSS/JS/image và admin chunks.
8. Test axe/Playwright nếu tool compatible.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Critical accessibility violations được xử lý hoặc documented exception.
- [ ] Public core content usable without JS.
- [ ] Performance budgets có CI gate hợp lý.
- [ ] Không hy sinh fidelity vô cớ.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && npm run build`
- `cd Admin && npm run build:laravel`
- `npx playwright test accessibility (nếu configured)`
- `Lighthouse command/report`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P46.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 46 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P47.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P47.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Critical accessibility violations được xử lý hoặc documented exception.
- [ ] Public core content usable without JS.
- [ ] Performance budgets có CI gate hợp lý.
- [ ] Không hy sinh fidelity vô cớ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
