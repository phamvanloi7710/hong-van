# PROMPT 31 — ROUTING PUBLIC VÀ CÁC TRANG LÕI

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản.

## Điều kiện tiên quyết

1. P28–P30 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P31 — Routing public và các trang lõi
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P31.
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
Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản.

NHIỆM VỤ BẮT BUỘC:
1. Tạo route resolver cho home, page slug, locale strategy, canonical trailing slash và reserved paths.
2. Tạo core page records/templates: home, giới thiệu, liên hệ, sản phẩm, dịch vụ, vận chuyển, kho bãi, tin tức, năng lực; nội dung có thể trống/seed demo có nhãn.
3. Unpublished/draft không public; preview chỉ signed.
4. Slug đổi tạo redirect 301 theo policy; deleted có 404/410.
5. Tạo branded 404/500/maintenance bằng Blade, không rò lỗi.
6. Breadcrumb từ page/menu/entity.
7. Route cache compatible.
8. Test collision với `/admin`, `/api`, `/preview`, assets và entity routes.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Page publish truy cập được SSR.
- [ ] Draft không lộ.
- [ ] Reserved path không bị page chiếm.
- [ ] Canonical/redirect đúng.
- [ ] Error pages an toàn.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan route:list`
- `cd BackEnd && php artisan route:cache`
- `cd BackEnd && php artisan test --filter=PublicPageRouting`
- `cd BackEnd && php artisan route:clear`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P31.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 31 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P32.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P32.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Page publish truy cập được SSR.
- [ ] Draft không lộ.
- [ ] Reserved path không bị page chiếm.
- [ ] Canonical/redirect đúng.
- [ ] Error pages an toàn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
