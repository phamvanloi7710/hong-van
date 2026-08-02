# PROMPT 56 — TÀI LIỆU CUỐI, BÀN GIAO VÀ KẾ HOẠCH BẢO TRÌ

**Phase:** 09 — Launch  
**Flag:** `REQUIRED`

## Mục tiêu

Đóng dự án với tài liệu vận hành, developer onboarding, admin guide, schema/API/page builder/media guide và backlog.

## Điều kiện tiên quyết

1. P55 DONE hoặc release candidate được chủ dự án chấp nhận.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P56 — Tài liệu cuối, bàn giao và kế hoạch bảo trì
PHẠM VI: 09 — Launch
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P56.
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
Đóng dự án với tài liệu vận hành, developer onboarding, admin guide, schema/API/page builder/media guide và backlog.

NHIỆM VỤ BẮT BUỘC:
1. Cập nhật README/START_HERE từ blueprint thành hướng dẫn project thật.
2. Hoàn thiện local setup, environment variables, build/test/deploy/backup/restore/monitoring.
3. Tạo admin user guide: products, pages/builder, media, leads, SEO, theme, users/permissions.
4. Tạo Page Builder block catalog và hướng dẫn thêm block mới an toàn.
5. Tạo API/OpenAPI docs, DB diagram/schema list, permissions matrix.
6. Tạo source integration notes/license.
7. Tạo maintenance calendar: dependency patch, security review, backup restore drill, content/SEO review, log retention.
8. Tạo known issues/deferred/backlog, không ghi mọi thứ hoàn hảo.
9. Đặt `docs/CODEX_STATE.md` status `DELIVERED`, cập nhật ledger và release info.
10. Chạy final verification từ docs trên checkout sạch hoặc ghi rõ phần chưa tái hiện.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Người mới có thể setup theo docs.
- [ ] Admin guide phản ánh UI thật.
- [ ] Không có secret.
- [ ] Deferred/risk minh bạch.
- [ ] Final test/build links/results được ghi.

KIỂM TRA TỐI THIỂU:
- `final local/staging setup verification`
- `link checker docs nếu có`
- `git status clean`
- `release tag check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P56.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 56 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = N/A.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không phát sinh prompt mới ngoài phạm vi bàn giao.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Người mới có thể setup theo docs.
- [ ] Admin guide phản ánh UI thật.
- [ ] Không có secret.
- [ ] Deferred/risk minh bạch.
- [ ] Final test/build links/results được ghi.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
