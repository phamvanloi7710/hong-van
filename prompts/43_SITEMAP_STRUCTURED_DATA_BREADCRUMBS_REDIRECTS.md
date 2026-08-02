# PROMPT 43 — SITEMAP, STRUCTURED DATA, BREADCRUMB VÀ REDIRECT

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả.

## Điều kiện tiên quyết

1. P42 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P43 — Sitemap, structured data, breadcrumb và redirect
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P43.
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
Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả.

NHIỆM VỤ BẮT BUỘC:
1. Tạo sitemap index và sitemap theo entity/locale, chỉ published/canonical, cache và regenerate/invalidate hợp lý.
2. Tạo robots.txt quản trị.
3. Tạo redirect manager `hongvan_redirects`: exact path, locale, status 301/302/410, loop/collision detection.
4. Structured data builders: Organization/LocalBusiness từ settings thật; WebSite; BreadcrumbList; Product; Article; Service; FAQ khi hợp lệ.
5. Product Offer chỉ khi giá public xác định; contact/market/dealer không khai price 0.
6. Hreflang/x-default khi locale enabled.
7. Admin UI redirects/sitemap health/schema preview.
8. Test redirect loop, reserved path, schema JSON encoding/XSS và sitemap exclusion.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Sitemap valid và không lộ draft.
- [ ] Schema không có dữ liệu giả.
- [ ] Redirect loop bị chặn.
- [ ] Breadcrumb tương ứng UI.
- [ ] Price 0 không xuất hiện schema.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Sitemap`
- `cd BackEnd && php artisan test --filter=StructuredData`
- `cd BackEnd && php artisan test --filter=Redirect`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P43.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 43 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P44.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P44.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Sitemap valid và không lộ draft.
- [ ] Schema không có dữ liệu giả.
- [ ] Redirect loop bị chặn.
- [ ] Breadcrumb tương ứng UI.
- [ ] Price 0 không xuất hiện schema.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
