# PROMPT 18 — KHỞI TẠO FRONTEND PUBLIC BẰNG LARAVEL BLADE

**Phase:** 03 — Media & Frontend  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo shell Blade SSR, asset pipeline, layout và component primitives trung tính để không phụ thuộc FrontEndTemplate chưa có.

## Điều kiện tiên quyết

1. P04, P13–P16 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P18 — Khởi tạo frontend public bằng Laravel Blade
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P18.
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
Tạo shell Blade SSR, asset pipeline, layout và component primitives trung tính để không phụ thuộc FrontEndTemplate chưa có.

NHIỆM VỤ BẮT BUỘC:
1. Thiết lập Vite entry public CSS/JS, layout `public`, semantic header/main/footer và skip link.
2. Tạo design token CSS cơ sở: colors semantic, typography, spacing, radius, shadow, container, breakpoints; giữ trung tính, dễ thay ở P19/P20.
3. Tạo Blade components cơ sở: button, link, image via Media, heading, container, breadcrumbs, form fields, alert.
4. Tạo route home placeholder từ settings, 404/500 minimal và legal page placeholders.
5. Không hardcode company contact.
6. Thiết lập asset version/cache and CSP-compatible scripts.
7. Thêm responsive baseline và accessibility focus.
8. Tạo frontend smoke tests: HTML content, title, language, no JS dependency for core text.
9. Document cách port FrontEndTemplate mà không phá Blade contracts.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Home server-rendered.
- [ ] Không có SPA public.
- [ ] View không query DB trực tiếp.
- [ ] Components dùng design tokens.
- [ ] FrontEndTemplate chưa có vẫn build/run được mà không giả là final design.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && npm ci && npm run build`
- `cd BackEnd && php artisan test --filter=PublicFrontend`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P18.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 18 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P19.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P19.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Home server-rendered.
- [ ] Không có SPA public.
- [ ] View không query DB trực tiếp.
- [ ] Components dùng design tokens.
- [ ] FrontEndTemplate chưa có vẫn build/run được mà không giả là final design.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
