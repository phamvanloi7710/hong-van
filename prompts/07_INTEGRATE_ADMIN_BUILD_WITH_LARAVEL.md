# PROMPT 07 — TÍCH HỢP BUILD ANGULAR VÀO LARAVEL

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`.

## Điều kiện tiên quyết

1. P06 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P07 — Tích hợp build Angular vào Laravel
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P07.
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
Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`.

NHIỆM VỤ BẮT BUỘC:
1. Kiểm tra Angular builder output thực tế của v22.1; không giả định cấu trúc cũ.
2. Cấu hình base href/deploy path `/admin/` và production environment `/api/admin/v1`.
3. Tạo `Admin/tools/sync-to-laravel` đa nền tảng hoặc npm script để xóa có guard đúng output cũ rồi copy build mới vào `BackEnd/public/admin/browser/`.
4. Tạo `scripts/build-admin.ps1` và `.sh` gọi `npm ci` khi cần, lint/test tùy mode, build production và sync.
5. Thêm Laravel/Nginx-friendly fallback route hoặc response file cho `/admin/{path?}` mà không nuốt `/api`, `/preview`, static assets hoặc public routes.
6. Thêm cache headers: index không cache dài; hashed assets cache immutable.
7. Tạo smoke test xác nhận `/admin/` trả index và asset path hợp lệ sau build.
8. Document quy trình trong `docs/LOCAL_DEVELOPMENT.md`.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] `npm run build:laravel` tạo đúng output.
- [ ] Không chỉnh thủ công output build.
- [ ] Refresh một deep link admin không 404.
- [ ] Public Laravel route không bị admin catch-all chiếm.
- [ ] Source map production theo policy.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run build:laravel`
- `cd BackEnd && php artisan test --filter=AdminSpa`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P07.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 07 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P08.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P08.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] `npm run build:laravel` tạo đúng output.
- [ ] Không chỉnh thủ công output build.
- [ ] Refresh một deep link admin không 404.
- [ ] Public Laravel route không bị admin catch-all chiếm.
- [ ] Source map production theo policy.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
