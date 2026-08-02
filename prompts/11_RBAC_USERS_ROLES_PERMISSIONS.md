# PROMPT 11 — QUẢN LÝ NGƯỜI DÙNG, VAI TRÒ VÀ QUYỀN

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default.

## Điều kiện tiên quyết

1. P10 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P11 — Quản lý người dùng, vai trò và quyền
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P11.
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
Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/models cho `hongvan_roles`, `hongvan_permissions`, pivots và optional user overrides; tất cả prefix.
2. Định nghĩa permission namespace theo module và action: view, create, update, delete, restore, publish, export, manage_settings.
3. Seed Super Admin an toàn bằng env/command, không hardcode password trong source.
4. Tạo policies/gates và middleware; Super Admin bypass phải explicit, audit và test.
5. Tạo CRUD API users/roles/permissions, pagination/filter, activate/lock, reset sessions.
6. Tạo Angular feature identity với routes lazy, tables/forms/dialogs theo template.
7. Tạo permission guard và structural directive/utility để ẩn/disable UI, nhưng backend vẫn là nguồn chân lý.
8. Ngăn user tự gỡ role cuối cùng có quyền quản trị hoặc xóa chính mình theo policy rõ.
9. Audit thay đổi role/permission/user.
10. Tạo permission matrix tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] User không quyền nhận 403 dù gọi API trực tiếp.
- [ ] UI phản ánh quyền sau refresh.
- [ ] Không thể làm mất Super Admin cuối cùng.
- [ ] Tất cả bảng prefix.
- [ ] Permission seed idempotent.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --seed --env=testing`
- `cd BackEnd && php artisan test --filter=Permission`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P11.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 11 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P12.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P12.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] User không quyền nhận 403 dù gọi API trực tiếp.
- [ ] UI phản ánh quyền sau refresh.
- [ ] Không thể làm mất Super Admin cuối cùng.
- [ ] Tất cả bảng prefix.
- [ ] Permission seed idempotent.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
