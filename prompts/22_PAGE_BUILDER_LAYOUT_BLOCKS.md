# PROMPT 22 — BLOCK LAYOUT NỀN

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens.

## Điều kiện tiên quyết

1. P21 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P22 — Block layout nền
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P22.
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
Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens.

NHIỆM VỤ BẮT BUỘC:
1. Triển khai Section, Container, Stack, Grid, Columns, Spacer, Divider; chỉ thêm Tabs/Accordion nếu template contract đã sẵn sàng.
2. Mỗi block có registry schema, defaults, responsive style allowlist, Blade view, sample fixture và tests.
3. Định nghĩa allowed parent/children và max nesting.
4. Columns/Grid responsive dùng preset/layout tokens, không cho raw CSS grid.
5. Section background hỗ trợ color token/media/gradient allowlist nếu theme cho phép.
6. Spacing chỉ dùng scale token hoặc bounded custom values.
7. Renderer semantic và không tạo div thừa quá mức.
8. Tạo block catalog docs với props và examples.
9. Test nested invalid, mobile stack và escaping.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Layout blocks render trong public Blade.
- [ ] Không arbitrary CSS.
- [ ] Nested constraints rõ.
- [ ] Mobile behavior test.
- [ ] Preview fixture tạo được.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=LayoutBlock`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P22.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 22 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P23.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P23.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Layout blocks render trong public Blade.
- [ ] Không arbitrary CSS.
- [ ] Nested constraints rõ.
- [ ] Mobile behavior test.
- [ ] Preview fixture tạo được.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
