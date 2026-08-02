# PROMPT 30 — MENU, HEADER, FOOTER VÀ GLOBAL REGIONS

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning.

## Điều kiện tiên quyết

1. P28 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P30 — Menu, header, footer và global regions
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P30.
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
Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning.

NHIỆM VỤ BẮT BUỘC:
1. Tạo menus/menu items nested với locale, type internal/external/entity/anchor, order, target/rel, permission/publish status.
2. Validate cycle/depth, URL protocol và broken entity reference.
3. Tạo global regions: header, footer, top bar, floating contact, footer columns; document/block versioning như page nhưng scope riêng.
4. Cho theme/page chọn region version hoặc dùng site default.
5. Angular menu tree DnD và global region editor dùng Page Builder.
6. Public renderer cache menu/region và invalidate đúng.
7. Active state/breadcrumb semantics/accessibility.
8. Không cho external link javascript protocol.
9. Test nested menu, locale, publish, cache, broken reference.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Menu quản trị kéo thả được.
- [ ] Header/footer public lấy dữ liệu version published.
- [ ] Không hardcode navigation.
- [ ] Accessible mobile navigation.
- [ ] Region preview đúng frontend style.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Menu`
- `cd BackEnd && php artisan test --filter=GlobalRegion`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P30.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 30 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P31.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P31.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Menu quản trị kéo thả được.
- [ ] Header/footer public lấy dữ liệu version published.
- [ ] Không hardcode navigation.
- [ ] Accessible mobile navigation.
- [ ] Region preview đúng frontend style.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
