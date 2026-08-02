# PROMPT 53 — SECURITY REVIEW TOÀN HỆ THỐNG

**Phase:** 08 — Operations  
**Flag:** `REQUIRED`

## Mục tiêu

Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production.

## Điều kiện tiên quyết

1. P48–P52 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P53 — Security review toàn hệ thống
PHẠM VI: 08 — Operations
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P53.
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
Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production.

NHIỆM VỤ BẮT BUỘC:
1. Lập threat model: public forms, auth, RBAC, media uploads, Page Builder JSON/preview, rich text, redirects, settings secrets, exports, deployment.
2. Review source-to-sink cho XSS, SQL injection, path traversal, SSRF, upload/RCE, IDOR, CSRF, open redirect, privilege escalation, session fixation, postMessage.
3. Chạy dependency audits và static security tooling phù hợp; không coi scanner là bằng chứng duy nhất.
4. Kiểm tra rate limits, CSP, cookies, CORS, trusted hosts/proxies, debug, error leakage.
5. Review Page Builder không arbitrary Blade/PHP/JS/CSS; import limits.
6. Review media MIME/storage/public serving/SVG.
7. Review admin permissions bằng direct API tests.
8. Tạo findings severity/evidence/fix/test; sửa critical/high trong scope.
9. Tạo `docs/reports/P53_SECURITY_REVIEW.md` không chứa exploit secret.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Critical/high được fix hoặc production blocked rõ.
- [ ] Regression tests cho finding.
- [ ] No false claim 'secure tuyệt đối'.
- [ ] Threat model cập nhật.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && composer audit`
- `cd Admin && npm audit --omit=dev hoặc policy phù hợp`
- `security test suite`
- `manual permission/upload/preview checks`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P53.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 53 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P54.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P54.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Critical/high được fix hoặc production blocked rõ.
- [ ] Regression tests cho finding.
- [ ] No false claim 'secure tuyệt đối'.
- [ ] Threat model cập nhật.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
