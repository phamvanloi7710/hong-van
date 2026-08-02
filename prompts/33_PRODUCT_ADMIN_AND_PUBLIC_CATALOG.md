# PROMPT 33 — QUẢN TRỊ SẢN PHẨM VÀ CATALOG PUBLIC

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm.

## Điều kiện tiên quyết

1. P32 DONE.
2. P16 Media, P31 public routing DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P33 — Quản trị sản phẩm và catalog public
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P33.
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
Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm.

NHIỆM VỤ BẮT BUỘC:
1. Tạo admin API CRUD categories/brands/products/tags/attributes với translation, media ordering, status, bulk publish/archive.
2. Tạo Angular feature product theo template: list, filter, form tabs, media picker, pricing mode conditional fields, preview/publish.
3. Tạo public product category/list/detail Blade, pagination, filter crop/use/category/brand nếu dữ liệu có.
4. Hiển thị giá qua PriceViewModel; contact CTA khi không có giá.
5. Không hiển thị stock/cart/buy now.
6. Gắn product quote form với product context.
7. Tạo related products và structured content hooks.
8. Tạo Page Builder data source adapter product/category.
9. SEO/JSON-LD ở mức placeholder tích hợp P42/P43.
10. Test permissions, validation, public published visibility, filters và price UI.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Admin CRUD đầy đủ.
- [ ] Public chỉ thấy published.
- [ ] Price/contact đúng mọi mode.
- [ ] CTA gửi đúng product ID an toàn.
- [ ] No N+1.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Product`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build:laravel`
- `public product smoke/E2E`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P33.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 33 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P34.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P34.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Admin CRUD đầy đủ.
- [ ] Public chỉ thấy published.
- [ ] Price/contact đúng mọi mode.
- [ ] CTA gửi đúng product ID an toàn.
- [ ] No N+1.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
