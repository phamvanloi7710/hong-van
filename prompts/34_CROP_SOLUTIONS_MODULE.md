# PROMPT 34 — GIẢI PHÁP THEO CÂY TRỒNG

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO.

## Điều kiện tiên quyết

1. P33 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P34 — Giải pháp theo cây trồng
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P34.
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
Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/domain crop categories, crops, stages, solutions, translations, product links.
2. Admin CRUD với ordering, media, stage timeline, recommended products, content sections và publish.
3. Public listing/detail SSR theo cây trồng; mỗi stage có nội dung và sản phẩm phù hợp.
4. Không đưa khuyến cáo tuyệt đối hoặc số liệu chuyên môn giả trong seed.
5. Tạo Page Builder data source/block adapter.
6. Tạo internal links product ↔ crop solution.
7. Policies/audit/cache.
8. Test locale, publish, ordering, deleted product handling và no N+1.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Giải pháp có thể quản trị hoàn toàn.
- [ ] Public SSR và SEO-ready.
- [ ] Không hardcode kiến thức giả.
- [ ] Liên kết sản phẩm ổn khi entity archive.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=CropSolution`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P34.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 34 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P35.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P35.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Giải pháp có thể quản trị hoàn toàn.
- [ ] Public SSR và SEO-ready.
- [ ] Không hardcode kiến thức giả.
- [ ] Liên kết sản phẩm ổn khi entity archive.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
