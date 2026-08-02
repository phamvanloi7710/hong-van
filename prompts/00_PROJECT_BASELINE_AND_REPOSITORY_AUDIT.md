# PROMPT 00 — THIẾT LẬP BASELINE VÀ KIỂM KÊ REPOSITORY

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định.

## Điều kiện tiên quyết

1. Bộ prompt đã được giải nén tại root project.
2. Codex được mở ở đúng root có `AGENTS.md`.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P00 — Thiết lập baseline và kiểm kê repository
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P00.
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
Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định.

NHIỆM VỤ BẮT BUỘC:
1. Đọc `AGENTS.md`, `START_HERE.md`, `docs/PROJECT_CHARTER.md`, `docs/TECH_STACK_LOCK.md`, `docs/CODEX_WORKFLOW.md` và `docs/CODEX_STATE.md`.
2. Chạy `git status` và ghi nhận repository đã init hay chưa, branch hiện tại, file untracked/modified; tuyệt đối không xóa thay đổi người dùng.
3. Kiểm kê ở mức top-level: `BackEnd/`, `Admin/`, `Template/`, `FrontEndTemplate/`, `SourceIntegrations/StayHubMedia/`, `prompts/`, `docs/`.
4. Xác định hệ điều hành/shell, PHP, Composer, Node, npm, Git, MySQL client và Docker hiện có. Chỉ ghi version; không tự nâng cấp.
5. Xác minh tên công ty và phạm vi: catalog phân bón + vận chuyển + kho bãi + CMS/Page Builder + lead; không e-commerce.
6. Đánh dấu source gate ban đầu: template admin có/không; frontend template có/không; StayHub media source có/không.
7. Cập nhật `docs/CODEX_STATE.md` bằng dữ liệu thật, không sửa các quyết định kiến trúc.
8. Tạo báo cáo baseline ngắn tại `docs/reports/P00_BASELINE.md` gồm môi trường, source gates, blocker và đề xuất prompt kế tiếp.

KHÔNG ĐƯỢC:
- Không cài framework.
- Không sửa Template/FrontEndTemplate/SourceIntegrations.
- Không chạy prompt 01.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có file source nào bị chỉnh ngoài `docs/CODEX_STATE.md` và báo cáo P00.
- [ ] Trạng thái Git và công cụ được ghi đúng từ lệnh thực tế.
- [ ] Ba external source gate có trạng thái rõ: READY, MISSING hoặc INVALID.
- [ ] `docs/CODEX_STATE.md` đặt `last_completed_prompt: 00` và `next_prompt: 01_EXTERNAL_SOURCE_INVENTORY`.

KIỂM TRA TỐI THIỂU:
- `git status --short --branch`
- `php -v`
- `composer --version`
- `node --version`
- `npm --version`
- `git --version`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P00.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 00 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P01.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P01.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có file source nào bị chỉnh ngoài `docs/CODEX_STATE.md` và báo cáo P00.
- [ ] Trạng thái Git và công cụ được ghi đúng từ lệnh thực tế.
- [ ] Ba external source gate có trạng thái rõ: READY, MISSING hoặc INVALID.
- [ ] `docs/CODEX_STATE.md` đặt `last_completed_prompt: 00` và `next_prompt: 01_EXTERNAL_SOURCE_INVENTORY`.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
