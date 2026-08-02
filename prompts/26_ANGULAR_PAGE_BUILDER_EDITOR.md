# PROMPT 26 — EDITOR KÉO THẢ PAGE BUILDER TRONG ANGULAR

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo.

## Điều kiện tiên quyết

1. P21 registry API DONE.
2. P06 admin template DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P26 — Editor kéo thả Page Builder trong Angular
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P26.
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
Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo.

NHIỆM VỤ BẮT BUỘC:
1. Tạo lazy feature page-builder theo cấu trúc template admin.
2. Load block registry metadata từ server và cache theo version.
3. Thiết kế typed PageDocument models, immutable operations add/move/reorder/duplicate/delete/update.
4. Dùng Angular CDK drag-drop hoặc primitive tương thích template; enforce allowed parent/children cả client và server.
5. UI gồm palette/search/category, tree/layers, canvas iframe host placeholder, properties panel, breadcrumbs selection, toolbar.
6. Tạo dynamic property editor theo schema control allowlist; không eval schema/code.
7. Undo/redo có bounded history; dirty state; keyboard shortcuts; confirm navigation.
8. Responsive device modes desktop/tablet/mobile và visibility/style overrides.
9. Permission view/edit/publish tách rõ.
10. Autosave orchestration contract nhưng chưa live preview hoàn chỉnh trước P27.
11. Unit tests cho document operations, nested DnD, duplicate id prevention và undo/redo.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Editor build pass.
- [ ] Không dùng `any` cho document core.
- [ ] Invalid nesting bị chặn UI và vẫn được server reject.
- [ ] Undo/redo ổn định.
- [ ] UI đúng admin template.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P26.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 26 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P27.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P27.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Editor build pass.
- [ ] Không dùng `any` cho document core.
- [ ] Invalid nesting bị chặn UI và vẫn được server reject.
- [ ] Undo/redo ổn định.
- [ ] UI đúng admin template.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
