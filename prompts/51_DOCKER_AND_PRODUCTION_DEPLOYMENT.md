# PROMPT 51 — DOCKER VÀ TRIỂN KHAI PRODUCTION

**Phase:** 08 — Operations  
**Flag:** `REQUIRED`

## Mục tiêu

Chuẩn hóa môi trường Nginx/PHP-FPM/queue/scheduler/MySQL/Redis và quy trình deploy an toàn.

## Điều kiện tiên quyết

1. P50 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P51 — Docker và triển khai production
PHẠM VI: 08 — Operations
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P51.
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
Chuẩn hóa môi trường Nginx/PHP-FPM/queue/scheduler/MySQL/Redis và quy trình deploy an toàn.

NHIỆM VỤ BẮT BUỘC:
1. Tạo docker compose development và production reference hoặc deployment manifests theo môi trường mục tiêu; không hardcode secrets.
2. PHP 8.5 extensions đúng Laravel 13; Composer multi-stage; frontend build multi-stage nếu dùng.
3. Nginx public root `BackEnd/public`, static admin/public assets, PHP routes, security deny dotfiles.
4. Tách app, queue, scheduler; healthchecks; graceful restart.
5. MySQL 8.4/Redis private network, persistent volumes, charset/timezone.
6. Storage media strategy local volume/S3; `storage:link` đúng.
7. Production commands: composer install no-dev, migrations, cache, queue restart, admin artifact.
8. Zero/minimal downtime và rollback plan.
9. Tạo staging smoke.
10. Document Ubuntu deployment và Windows local differences.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Containers start/healthy ở môi trường test.
- [ ] DB/Redis không public.
- [ ] Admin deep link/public routes hoạt động.
- [ ] Queue/scheduler chạy.
- [ ] No secret in image/history.

KIỂM TRA TỐI THIỂU:
- `docker compose config`
- `docker compose build`
- `docker compose up -d`
- `health/smoke commands`
- `docker compose down (không xóa data trừ test volume)`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P51.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 51 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P52.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P52.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Containers start/healthy ở môi trường test.
- [ ] DB/Redis không public.
- [ ] Admin deep link/public routes hoạt động.
- [ ] Queue/scheduler chạy.
- [ ] No secret in image/history.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
