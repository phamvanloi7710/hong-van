# PROMPT 15 — NHẬT KÝ HOẠT ĐỘNG VÀ HARDENING NỀN

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây audit trail, security headers, rate limiting và redaction trước các module nội dung.

## Điều kiện tiên quyết

1. P10–P14 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P15 — Nhật ký hoạt động và hardening nền
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P15.
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
Xây audit trail, security headers, rate limiting và redaction trước các module nội dung.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_audit_logs` append-only: actor, action, subject type/public id, before/after redacted diff, IP/user agent hash/metadata, request ID, timestamp.
2. Tạo audit service/event subscriber cho auth, identity, settings và chuẩn cho module sau.
3. Không audit password/token/cookie/body file hoặc secret plain.
4. Tạo API/UI xem audit theo permission, filter allowlist, không sửa/xóa thông thường.
5. Thiết lập CSP, X-Content-Type-Options, Referrer-Policy, HSTS production, frame rules cho admin/preview có ngoại lệ tối thiểu.
6. Rate limit login, public forms placeholder, upload, preview session.
7. Thiết lập trusted proxies/hosts từ env.
8. Tạo security logging channel và retention config.
9. Test audit integrity, redaction, permission và headers.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Thao tác nhạy cảm tạo audit.
- [ ] Audit không chứa secret.
- [ ] Admin thường không sửa/xóa log.
- [ ] Preview iframe vẫn hoạt động theo frame/CSP design.
- [ ] Headers test pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Audit`
- `cd BackEnd && php artisan test --filter=SecurityHeaders`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P15.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 15 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P16.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P16.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Thao tác nhạy cảm tạo audit.
- [ ] Audit không chứa secret.
- [ ] Admin thường không sửa/xóa log.
- [ ] Preview iframe vẫn hoạt động theo frame/CSP design.
- [ ] Headers test pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
