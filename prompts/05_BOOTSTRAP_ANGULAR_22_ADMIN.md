# PROMPT 05 — KHỞI TẠO ANGULAR 22.1 ADMIN

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready.

## Điều kiện tiên quyết

1. P04 DONE.
2. Node/npm tương thích Angular 22.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P05 — Khởi tạo Angular 22.1 admin
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P05.
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
Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready.

NHIỆM VỤ BẮT BUỘC:
1. Xác nhận Angular 22.1 stable patch và compatibility Node/TypeScript.
2. Vì `Admin/` không rỗng, chạy Angular CLI trong `.bootstrap/Admin` rồi merge an toàn vào `Admin/`, giữ AGENTS hiện có.
3. Khởi tạo standalone, routing, strict TypeScript, SCSS hoặc preprocessor đúng template; không thêm SSR cho admin.
4. Đặt project name `hongvan-admin`, prefix selector `hv` hoặc tên nhất quán đã ghi ADR.
5. Tạo khung `core/`, `shared/`, `features/` và lazy route placeholder; không dựng business UI.
6. Cấu hình environment typed cho API base `/api/admin/v1`, app base `/admin/`; không hardcode domain.
7. Thiết lập npm scripts `lint`, `test`, `build`, `build:laravel` placeholder có fail rõ cho bước P07.
8. Giữ lockfile và engine requirement.
9. Xóa `.bootstrap/Admin` sau merge thành công.

KHÔNG ĐƯỢC:
- Không port template admin ở bước này.
- Không cài state library lớn khi chưa có nhu cầu.

TIÊU CHÍ NGHIỆM THU:
- [ ] `ng version` báo Angular/CLI 22.1.x cùng dòng.
- [ ] `npm ci`, test và build mặc định pass.
- [ ] Strict mode bật.
- [ ] Không dùng NgModule architecture cũ nếu CLI không cần.
- [ ] AGENTS ở feature vẫn còn.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm ci`
- `cd Admin && npx ng version`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P05.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 05 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P06.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P06.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] `ng version` báo Angular/CLI 22.1.x cùng dòng.
- [ ] `npm ci`, test và build mặc định pass.
- [ ] Strict mode bật.
- [ ] Không dùng NgModule architecture cũ nếu CLI không cần.
- [ ] AGENTS ở feature vẫn còn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
