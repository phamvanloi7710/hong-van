# PROMPT 10 — XÁC THỰC ADMIN BẰNG SANCTUM COOKIE/SESSION

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin.

## Điều kiện tiên quyết

1. P07–P09 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P10 — Xác thực admin bằng Sanctum cookie/session
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P10.
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
Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin.

NHIỆM VỤ BẮT BUỘC:
1. Cài/cấu hình Sanctum phiên bản tương thích Laravel 13; publish migration và đổi bảng thành `hongvan_personal_access_tokens` dù admin chính dùng cookie.
2. Cấu hình stateful domains từ env, CSRF cookie, session cookie name có prefix Hồng Vân, SameSite/Secure/HttpOnly đúng môi trường.
3. Tạo API login/logout/me/forgot-password/reset-password với Form Requests và rate limits.
4. Regenerate session sau login; invalidate đúng sau logout/password reset.
5. Không lưu bearer token trong localStorage cho flow admin same-origin.
6. Tạo Angular auth service, session bootstrap, login form dùng đúng template, auth guard và interceptor CSRF/401.
7. Thêm loading/error state, không phân biệt email tồn tại ở forgot-password.
8. Thiết lập account active/locked fields và kiểm tra login.
9. Audit login success/failure ở mức không lộ password.
10. Viết backend tests và Angular tests.

KHÔNG ĐƯỢC:
- Không tắt CSRF.
- Không chuyển sang JWT chỉ để đơn giản.

TIÊU CHÍ NGHIỆM THU:
- [ ] Login Angular hoạt động bằng cookie + CSRF.
- [ ] Refresh giữ session hợp lệ.
- [ ] Logout vô hiệu session.
- [ ] Inactive/locked user bị chặn.
- [ ] Không có token nhạy cảm trong localStorage/log.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Auth`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P10.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 10 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P11.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P11.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Login Angular hoạt động bằng cookie + CSRF.
- [ ] Refresh giữ session hợp lệ.
- [ ] Logout vô hiệu session.
- [ ] Inactive/locked user bị chặn.
- [ ] Không có token nhạy cảm trong localStorage/log.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
