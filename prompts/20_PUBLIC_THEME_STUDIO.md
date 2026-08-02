# PROMPT 20 — THEME STUDIO CHO WEBSITE PUBLIC

**Phase:** 03 — Media & Frontend  
**Flag:** `REQUIRED`

## Mục tiêu

Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin.

## Điều kiện tiên quyết

1. P18 DONE; P19 có thể DONE hoặc DEFERRED.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P20 — Theme Studio cho website public
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P20.
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
Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_themes`, `hongvan_theme_versions`, active/published version và schema token allowlist.
2. Token gồm colors semantic, fonts allowlist, sizes, spacing scale, radii, shadows, container widths, buttons, headings, section gaps và animation presets.
3. Không cho arbitrary CSS/JS mặc định.
4. Tạo API draft/preview/publish/rollback theme với permission.
5. Tạo Angular Theme Studio theo style admin template; property controls typed.
6. Tạo CSS variable compiler server-side hoặc build runtime an toàn; cache output theo version.
7. Preview theme qua signed preview và Page Builder renderer.
8. Nếu FrontEndTemplate đã port, khởi tạo token từ template; nếu chưa, dùng neutral base và ghi cần remap sau P19.
9. Audit publish/rollback.
10. Test invalid token, versioning và cache invalidation.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Theme public tách khỏi user admin theme.
- [ ] Published pages dùng published theme version.
- [ ] Rollback không mất lịch sử.
- [ ] Không inject CSS/JS tùy ý.
- [ ] Preview và public dùng cùng token compiler.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Theme`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P20.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 20 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P21.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P21.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Theme public tách khỏi user admin theme.
- [ ] Published pages dùng published theme version.
- [ ] Rollback không mất lịch sử.
- [ ] Không inject CSS/JS tùy ý.
- [ ] Preview và public dùng cùng token compiler.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
