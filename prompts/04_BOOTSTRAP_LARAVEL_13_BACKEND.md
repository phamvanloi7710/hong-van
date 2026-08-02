# PROMPT 04 — KHỞI TẠO LARAVEL 13 BACKEND

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Khởi tạo Laravel 13 sạch trong `BackEnd/`, pin PHP 8.5 và chuẩn bị nền tảng Blade/API mà không phá AGENTS hiện có.

## Điều kiện tiên quyết

1. P03 DONE.
2. PHP/Composer tương thích hoặc blocker đã được giải quyết.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P04 — Khởi tạo Laravel 13 backend
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P04.
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
Khởi tạo Laravel 13 sạch trong `BackEnd/`, pin PHP 8.5 và chuẩn bị nền tảng Blade/API mà không phá AGENTS hiện có.

NHIỆM VỤ BẮT BUỘC:
1. Xác nhận stable patch mới nhất trong dòng Laravel 13 và PHP 8.5 bằng nguồn chính thức hoặc Composer metadata.
2. Vì `BackEnd/` không rỗng, tạo Laravel trong `.bootstrap/BackEnd`, sau đó merge an toàn vào `BackEnd/`, giữ `AGENTS.md` và `.ai/guidelines/`.
3. Thiết lập application name `HongVan`, locale mặc định `vi`, fallback `en`, timezone ứng dụng `Asia/Ho_Chi_Minh`; DB vẫn lưu UTC.
4. Cấu hình `.env.example` cho MySQL database mặc định `hongvan_platform`, Redis, mail, filesystem; để password rỗng placeholder chứ không hardcode production.
5. Giữ Blade + Vite; không cài Inertia/Livewire/React/Vue starter kit.
6. Tạo route group khung cho `web.php`, `api.php`, `admin.php`, `preview.php`; chưa tạo business endpoint.
7. Cài Laravel Boost dev dependency nếu tương thích Laravel 13 và chạy installer ở chế độ phù hợp, bảo toàn guideline project.
8. Cấu hình Pint và test runner mặc định; thêm Larastan/PHPStan tương thích sau khi kiểm tra version.
9. Tạo health route an toàn và một smoke test.
10. Xóa `.bootstrap/BackEnd` sau merge thành công.

KHÔNG ĐƯỢC:
- Không cấu hình production secret.
- Không tạo bảng nghiệp vụ.
- Không chạm Angular.

TIÊU CHÍ NGHIỆM THU:
- [ ] `php artisan --version` là Laravel 13.x.
- [ ] `composer.json` yêu cầu PHP ^8.5 hoặc constraint tương thích quyết định đã ghi.
- [ ] Trang welcome/health và test smoke hoạt động.
- [ ] Không có starter kit SPA ngoài phạm vi.
- [ ] Không mất AGENTS/guidelines.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && composer validate`
- `cd BackEnd && php artisan about`
- `cd BackEnd && php artisan test`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P04.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 04 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P05.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P05.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] `php artisan --version` là Laravel 13.x.
- [ ] `composer.json` yêu cầu PHP ^8.5 hoặc constraint tương thích quyết định đã ghi.
- [ ] Trang welcome/health và test smoke hoạt động.
- [ ] Không có starter kit SPA ngoài phạm vi.
- [ ] Không mất AGENTS/guidelines.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
