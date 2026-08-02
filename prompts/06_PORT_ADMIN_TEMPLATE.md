# PROMPT 06 — PORT TEMPLATE ADMIN VÀO ANGULAR 22

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu.

## Điều kiện tiên quyết

1. P05 DONE.
2. Gate Admin Template = READY; nếu thiếu thì BLOCKED, không deferred toàn project.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P06 — Port template admin vào Angular 22
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P06.
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
Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu.

NHIỆM VỤ BẮT BUỘC:
1. Đọc inventory P01 và kiểm tra lại manifest/template source có thay đổi.
2. Lập mapping cụ thể: app shell, header, sidebar, menu, breadcrumb, footer, auth layout, typography, icon, assets, theme service, demo pages.
3. Nếu template khác Angular 22, port từng layout/component vào Angular 22 thay vì nâng/sửa source trong `Template/`.
4. Giữ visual fidelity: spacing, breakpoints, menus, animations cần thiết và responsive states.
5. Loại bỏ demo business pages không dùng nhưng giữ component showcase cần để đối chiếu.
6. Tách `Admin/src/app/core/layout` và shared components đúng ranh giới.
7. Port theme settings UI của template nhưng chưa lưu server; dùng adapter local tạm với contract sẽ thay ở P12.
8. Chuẩn hóa asset import và license notices.
9. Tạo route `/admin` shell, `/admin/login` auth shell và dashboard placeholder.
10. Tạo visual checklist/screenshots nội bộ nếu tool có sẵn; không dùng screenshot làm source duy nhất.

KHÔNG ĐƯỢC:
- Không thay template bằng UI khác.
- Không giữ hardcode demo credentials/domain.

TIÊU CHÍ NGHIỆM THU:
- [ ] Source `Template/` có diff bằng 0.
- [ ] Admin build pass Angular 22.
- [ ] Layout desktop/mobile tương đồng template.
- [ ] Theme panel cơ bản hoạt động tạm.
- [ ] Không kéo theo API/demo backend hoặc secret của template.

KIỂM TRA TỐI THIỂU:
- `git diff -- Template`
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P06.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 06 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P07.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P07.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Source `Template/` có diff bằng 0.
- [ ] Admin build pass Angular 22.
- [ ] Layout desktop/mobile tương đồng template.
- [ ] Theme panel cơ bản hoạt động tạm.
- [ ] Không kéo theo API/demo backend hoặc secret của template.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
