# PROMPT 49 — QA ANGULAR, E2E VÀ VISUAL REGRESSION

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages.

## Điều kiện tiên quyết

1. P48 DONE.
2. Admin build integration DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P49 — QA Angular, E2E và visual regression
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P49.
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
Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages.

NHIỆM VỤ BẮT BUỘC:
1. Chạy lint, unit tests và production build.
2. Thiết lập/hoàn thiện Playwright với auth state an toàn, test database isolation và base URL config.
3. E2E: login/logout, RBAC, theme per user, product CRUD, page builder preview/publish/rollback, media picker, lead workflow, SEO edit.
4. Visual snapshots cho admin shell, media manager (nếu P17 done), Page Builder canvas, public home/product/service desktop/tablet/mobile.
5. Không update snapshots mù quáng; review diff.
6. Accessibility smoke trong E2E.
7. Kiểm tra console errors, failed network, source map/asset path.
8. Tạo `docs/reports/P49_FRONTEND_QA.md`.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Lint/unit/build pass.
- [ ] Critical E2E pass.
- [ ] Visual diffs được review.
- [ ] No console error trong workflows.
- [ ] Deferred source parity được ghi, không che.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`
- `cd Admin && npx playwright test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P49.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 49 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P50.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P50.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Lint/unit/build pass.
- [ ] Critical E2E pass.
- [ ] Visual diffs được review.
- [ ] No console error trong workflows.
- [ ] Deferred source parity được ghi, không che.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
