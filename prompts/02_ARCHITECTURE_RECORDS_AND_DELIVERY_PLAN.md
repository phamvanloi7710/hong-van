# PROMPT 02 — CHỐT ADR, MODULE MAP VÀ KẾ HOẠCH BÀN GIAO

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng.

## Điều kiện tiên quyết

1. P00–P01 DONE hoặc source gate đã được đánh dấu deferred.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P02 — Chốt ADR, module map và kế hoạch bàn giao
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P02.
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
Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng.

NHIỆM VỤ BẮT BUỘC:
1. Rà `docs/ARCHITECTURE.md`, `DATABASE_BLUEPRINT.md`, `PAGE_BUILDER_CONTRACT.md`, `API_CONVENTIONS.md`, `SECURITY_BASELINE.md`.
2. Tạo ADR riêng trong `docs/adr/` cho: monorepo, Laravel Blade public, Angular admin, Sanctum same-origin, explicit table prefix, Blade iframe preview, external-source read-only, no e-commerce.
3. Tạo `docs/MODULE_MAP.md` mô tả bounded context, owner data, public routes, admin routes, permissions và dependency.
4. Tạo `docs/DELIVERY_PHASES.md` nhóm 57 prompt thành milestones và source gates.
5. Tạo `docs/NON_FUNCTIONAL_REQUIREMENTS.md`: security, accessibility, performance, SEO, observability, backup, browser support, file upload limits configurable.
6. Định nghĩa Definition of Done dùng chung: code + migration + auth + test + docs + lint/build + state.
7. Không thay đổi kiến trúc đã accepted nếu chưa có mâu thuẫn; nếu có, tạo ADR `Proposed` thay vì âm thầm đổi.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Mỗi ADR có Context, Decision, Consequences, Status, Date.
- [ ] Module map không biến website thành ERP/WMS/TMS/e-commerce.
- [ ] Definition of Done áp dụng được cho backend và Angular.
- [ ] Không có thông tin công ty giả.

KIỂM TRA TỐI THIỂU:
- `find docs/adr -maxdepth 1 -type f`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P02.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 02 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P03.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P03.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Mỗi ADR có Context, Decision, Consequences, Status, Date.
- [ ] Module map không biến website thành ERP/WMS/TMS/e-commerce.
- [ ] Definition of Done áp dụng được cho backend và Angular.
- [ ] Không có thông tin công ty giả.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
