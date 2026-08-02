# PROMPT 24 — BLOCK DỮ LIỆU NGHIỆP VỤ

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý.

## Điều kiện tiên quyết

1. P21–P23 DONE. Domain chưa tồn tại có thể dùng data-source contracts/fakes, sau module tương ứng hoàn thiện adapter.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P24 — Block dữ liệu nghiệp vụ
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P24.
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
Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa DataSourceRegistry server: products, product categories, crop solutions, services, vehicles/fleet, routes, warehouses, stats, partners, certifications, projects, posts.
2. Mỗi binding chỉ cho filter/sort/limit/preset allowlist; không nhận raw SQL/column.
3. Triển khai block types: hero, product grid, category grid, crop grid, service grid, fleet, route list, warehouse cards, stats, partner logos, certificate list, project list, post list, CTA, breadcrumb.
4. Với domain chưa có, renderer trả empty state có kiểm soát trong preview và không crash public.
5. Tách query/data loading khỏi Blade.
6. Thêm cache dependency tags để entity update invalidates page fragment.
7. Thiết kế preview sample data option chỉ trong preview, không rò ra production.
8. Test injection, limit max, empty state, unpublished entity exclusion và locale.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không query tùy ý từ page document.
- [ ] Chỉ entity published xuất hiện public.
- [ ] Empty data không phá layout.
- [ ] Cache invalidation contract rõ.
- [ ] Dynamic block query không N+1.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=DynamicBlock`
- `cd BackEnd && vendor/bin/phpstan analyse app/Domain/PageBuilder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P24.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 24 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P25.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P25.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không query tùy ý từ page document.
- [ ] Chỉ entity published xuất hiện public.
- [ ] Empty data không phá layout.
- [ ] Cache invalidation contract rõ.
- [ ] Dynamic block query không N+1.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
