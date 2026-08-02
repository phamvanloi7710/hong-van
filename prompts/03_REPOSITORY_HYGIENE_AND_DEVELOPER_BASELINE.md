# PROMPT 03 — THIẾT LẬP REPOSITORY HYGIENE VÀ BASELINE CÔNG CỤ

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework.

## Điều kiện tiên quyết

1. P02 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P03 — Thiết lập repository hygiene và baseline công cụ
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P03.
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
Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework.

NHIỆM VỤ BẮT BUỘC:
1. Kiểm tra `.editorconfig`, `.gitattributes`, `.gitignore` có bảo vệ source template/license, env, vendor, node_modules, build output và logs.
2. Tạo `.env.example` ở root chỉ chứa biến mô tả path/build chung; không chứa secret.
3. Tạo `docs/LOCAL_DEVELOPMENT.md` cho Windows và Linux, nhưng chưa giả định framework đã được cài.
4. Tạo `scripts/verify-prerequisites.ps1` và `.sh` để kiểm tra version PHP/Composer/Node/npm/Git và cảnh báo không tương thích; không tự cài.
5. Tạo `scripts/verify-readonly-sources.ps1` và `.sh` ghi hash/status để phát hiện source tham chiếu bị sửa.
6. Thiết lập commit message convention và branch convention trong `docs/GIT_WORKFLOW.md`.
7. Nếu Git chưa init, chỉ init khi working directory an toàn; không tự thêm remote.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Scripts fail-fast, không in secret, hỗ trợ path có khoảng trắng.
- [ ] Ignore không vô tình ignore `AGENTS.md` hoặc prompt/docs.
- [ ] Không commit build output hoặc source template theo mặc định.
- [ ] Baseline scripts chạy được trên shell hiện tại hoặc ghi rõ chưa test shell khác.

KIỂM TRA TỐI THIỂU:
- `git check-ignore -v Template/README_PLACE_ADMIN_TEMPLATE_HERE.md || true`
- `git diff --check`
- `scripts/verify-prerequisites.* (chạy bản phù hợp hệ điều hành)`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P03.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 03 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P04.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P04.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Scripts fail-fast, không in secret, hỗ trợ path có khoảng trắng.
- [ ] Ignore không vô tình ignore `AGENTS.md` hoặc prompt/docs.
- [ ] Không commit build output hoặc source template theo mặc định.
- [ ] Baseline scripts chạy được trên shell hiện tại hoặc ghi rõ chưa test shell khác.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
