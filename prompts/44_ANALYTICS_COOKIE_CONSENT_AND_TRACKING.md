# PROMPT 44 — ANALYTICS VÀ COOKIE CONSENT

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance.

## Điều kiện tiên quyết

1. P13 settings, P42 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P44 — Analytics và cookie consent
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P44.
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
Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế settings provider cho GA/Tag Manager/other approved, disabled by default.
2. Tạo consent categories necessary/analytics/marketing, banner/preferences, locale text và policy link.
3. Chỉ inject analytics script sau consent theo law/policy configuration; necessary cookie không cần consent giả.
4. Tạo `hongvan_consent_records` chỉ khi cần server record; giảm dữ liệu/retention.
5. Page Builder không cho arbitrary tracking scripts.
6. Admin analytics settings masked/validated.
7. Thêm event hooks lead submit/product view nhưng không gửi PII.
8. Test no-consent no-script, revoke, CSP nonce/hash compatibility.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Analytics disabled không tạo request ngoài.
- [ ] Không gửi PII.
- [ ] Consent persistence và revoke hoạt động.
- [ ] Không arbitrary script injection.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Consent`
- `frontend browser/E2E consent tests`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P44.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 44 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P45.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P45.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Analytics disabled không tạo request ngoài.
- [ ] Không gửi PII.
- [ ] Consent persistence và revoke hoạt động.
- [ ] Không arbitrary script injection.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
