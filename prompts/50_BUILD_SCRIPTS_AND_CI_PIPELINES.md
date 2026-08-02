# PROMPT 50 — BUILD REPRODUCIBLE VÀ CI

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile.

## Điều kiện tiên quyết

1. P48–P49 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P50 — Build reproducible và CI
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P50.
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
Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile.

NHIỆM VỤ BẮT BUỘC:
1. Hoàn thiện scripts PowerShell/shell: verify prerequisites, backend QA, admin QA, build/sync, smoke.
2. Tạo GitHub Actions hoặc CI vendor-neutral docs: backend job PHP 8.5 + MySQL 8.4 + Redis; admin Node compatible; E2E optional service.
3. CI chạy prefix check, migrations, tests, Pint, PHPStan, npm ci/lint/test/build, audits.
4. Cache dependencies đúng key lockfile, không cache secret/build sai.
5. Build artifact admin/public có checksum; không commit output nếu policy.
6. Source template folders ignored; CI không phụ thuộc source license nếu code đã port.
7. Secret scanning/dependency audit.
8. Branch protection recommendations.
9. Test workflow syntax và local scripts.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] CI từ checkout sạch có thể chạy.
- [ ] Không dùng npm install thay npm ci.
- [ ] Không chứa secret.
- [ ] Fail khi prefix/test/build fail.

KIỂM TRA TỐI THIỂU:
- `validate YAML`
- `run local verify scripts`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P50.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 50 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P51.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P51.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] CI từ checkout sạch có thể chạy.
- [ ] Không dùng npm install thay npm ci.
- [ ] Không chứa secret.
- [ ] Fail khi prefix/test/build fail.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
