# PROMPT 27 — LIVE PREVIEW BẰNG BLADE IFRAME

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Bảo đảm canvas admin render đúng markup/CSS public thông qua preview session ký và Redis.

## Điều kiện tiên quyết

1. P26 DONE.
2. P21–P25 renderers DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P27 — Live preview bằng Blade iframe
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P27.
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
Bảo đảm canvas admin render đúng markup/CSS public thông qua preview session ký và Redis.

NHIỆM VỤ BẮT BUỘC:
1. Tạo preview session API: create/update/refresh/close; payload validated, TTL và ownership.
2. Lưu document preview tạm Redis, không tạo DB version mỗi lần gõ.
3. Tạo signed route `/preview/page-builder/{token}` render cùng PageRenderer/theme/CSS public, header `noindex` và CSP.
4. Angular iframe host gửi update debounced và refresh/message event.
5. `postMessage` kiểm tra exact origin, session token, message type/schema; không dùng wildcard origin.
6. Tạo selection overlay/scroll-to-block bằng data attributes an toàn, không thay markup public đáng kể.
7. Error block preview hiển thị lỗi path cho editor nhưng public published không bao giờ dùng invalid document.
8. Handle session expiry/reconnect/network error.
9. Tạo tests ownership/token expiry/CSP/XSS và E2E preview parity.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Canvas và public dùng cùng renderer.
- [ ] Preview URL không truy cập được sau expiry hoặc user khác.
- [ ] Không ghi DB quá mức.
- [ ] postMessage an toàn.
- [ ] Responsive preview đúng CSS breakpoint.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PreviewSession`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npx playwright test page-builder-preview`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P27.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 27 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P28.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P28.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Canvas và public dùng cùng renderer.
- [ ] Preview URL không truy cập được sau expiry hoặc user khác.
- [ ] Không ghi DB quá mức.
- [ ] postMessage an toàn.
- [ ] Responsive preview đúng CSS breakpoint.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
