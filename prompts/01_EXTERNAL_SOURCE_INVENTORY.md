# PROMPT 01 — KIỂM KÊ TEMPLATE VÀ EXTERNAL SOURCE

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source.

## Điều kiện tiên quyết

1. Prompt 00 DONE.
2. `docs/CODEX_STATE.md` phản ánh source gates.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P01 — Kiểm kê template và external source
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P01.
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
Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source.

NHIỆM VỤ BẮT BUỘC:
1. Đọc root và các `AGENTS.md` trong `Template/`, `FrontEndTemplate/`, `SourceIntegrations/`.
2. Nếu `Template/` có source: tìm `package.json`, `angular.json`, entry points, layout, routing, menu, theme settings, icon/font/assets, auth screens và build commands; ghi version/dependency bằng cách đọc manifest, không cài.
3. Tạo `docs/inventories/ADMIN_TEMPLATE_INVENTORY.md` với cấu trúc, thành phần tái sử dụng, rủi ro nâng lên Angular 22 và license note.
4. Nếu `FrontEndTemplate/` có source: inventory page layouts, CSS system, JS plugins, components/sections, breakpoints, typography, header/footer và asset; ghi vào `docs/inventories/FRONTEND_TEMPLATE_INVENTORY.md`.
5. Nếu StayHub media source có mặt: inventory route, components, APIs, models, permissions, storage và behaviors; ghi vào `docs/inventories/STAYHUB_MEDIA_INVENTORY.md`.
6. Nếu một source thiếu, tạo inventory file ghi `DEFERRED — SOURCE MISSING`, đúng path cần bổ sung và không mô tả giả chức năng.
7. Tạo `docs/inventories/SOURCE_MAPPING_SUMMARY.md` nêu thứ gì port, thứ gì bỏ, thứ gì cần quyết định.
8. Cập nhật source gate trong `docs/CODEX_STATE.md` và task ledger.

KHÔNG ĐƯỢC:
- Không npm install/composer install trong source tham chiếu.
- Không copy source vào app ở bước này.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có thay đổi bên trong source read-only.
- [ ] Inventory dùng bằng chứng từ manifest/file thật.
- [ ] Source thiếu được đánh dấu deferred, không bị báo lỗi toàn project.
- [ ] Có mapping rõ giữa source và `Admin/`/`BackEnd/resources/`/Media domain.

KIỂM TRA TỐI THIỂU:
- `git diff -- Template FrontEndTemplate SourceIntegrations`
- `git status --short`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P01.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 01 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P02.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P02.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có thay đổi bên trong source read-only.
- [ ] Inventory dùng bằng chứng từ manifest/file thật.
- [ ] Source thiếu được đánh dấu deferred, không bị báo lỗi toàn project.
- [ ] Có mapping rõ giữa source và `Admin/`/`BackEnd/resources/`/Media domain.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
