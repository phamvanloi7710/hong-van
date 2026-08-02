# PROMPT 23 — BLOCK NỘI DUNG VÀ MEDIA

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai block nội dung phổ biến và media, với sanitization và accessibility.

## Điều kiện tiên quyết

1. P22 DONE.
2. P16 Media DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P23 — Block nội dung và media
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P23.
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
Triển khai block nội dung phổ biến và media, với sanitization và accessibility.

NHIỆM VỤ BẮT BUỘC:
1. Triển khai Heading, RichText, Button, Icon, List, Quote, Table, Badge/Card, Image, ImageText, Gallery, VideoEmbed, LogoCloud, FAQ.
2. Heading có level allowlist và rule tránh nhiều H1 mặc định.
3. RichText dùng editor output có schema/sanitizer server; chặn scripts/events/unsafe URLs.
4. Image chọn từ Media, bắt buộc alt hoặc decorative flag; responsive variants, width/height.
5. Video provider allowlist và privacy-friendly embed.
6. Link protocol/target/rel an toàn.
7. Gallery lazy loading và keyboard/accessibility.
8. FAQ có thể tạo structured data sau khi xác minh content.
9. Mỗi block có tests và fixture.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] XSS payload bị loại/reject.
- [ ] Media usage được ghi khi document save/publish.
- [ ] Alt/decorative validation.
- [ ] Markup accessible.
- [ ] No N+1 media queries.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=ContentBlock`
- `cd BackEnd && php artisan test --filter=MediaBlock`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P23.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 23 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P24.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P24.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] XSS payload bị loại/reject.
- [ ] Media usage được ghi khi document save/publish.
- [ ] Alt/decorative validation.
- [ ] Markup accessible.
- [ ] No N+1 media queries.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
