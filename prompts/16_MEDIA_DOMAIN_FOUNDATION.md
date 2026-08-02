# PROMPT 16 — XÂY DOMAIN MEDIA ĐỘC LẬP UI

**Phase:** 03 — Media & Frontend  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo hạ tầng media an toàn, API contract và picker interface để các module dùng ngay, trước khi clone UI StayHub.

## Điều kiện tiên quyết

1. P13–P15 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P16 — Xây domain Media độc lập UI
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P16.
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
Tạo hạ tầng media an toàn, API contract và picker interface để các module dùng ngay, trước khi clone UI StayHub.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations media folders, media, variants, tags, usage, operations theo blueprint.
2. Thiết kế Media model không lưu full public URL cố định; lưu disk/path/metadata/checksum/dimensions/MIME/size/status.
3. Upload service kiểm tra MIME thực, extension allowlist, size config, filename normalization và storage path server-generated.
4. Chặn SVG mặc định hoặc triển khai sanitizer riêng có test; chặn file thực thi.
5. Tạo queued image variant generation, thumbnail, webp/avif nếu môi trường hỗ trợ; giữ original theo policy.
6. Tạo APIs list/search/filter/sort/upload/metadata/move/trash/restore/delete với permission.
7. Tạo `MediaPickerContract` trong Angular và UI picker tối thiểu trung tính, không tuyên bố clone StayHub.
8. Tạo usage tracking để biết media đang được product/page/post/settings dùng.
9. Tạo storage abstraction local/S3 compatible qua Laravel Filesystem.
10. Tạo cleanup/retry và audit.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Upload file hợp lệ thành công và file nguy hiểm bị từ chối.
- [ ] Delete media đang dùng có cảnh báo/policy.
- [ ] Variant chạy queue và failure được ghi.
- [ ] API typed và permission test.
- [ ] UI tối thiểu chọn được media cho module sau.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Media`
- `cd BackEnd && php artisan queue:work --once --env=testing (nếu test queue thật)`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P16.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 16 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P17.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P17.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Upload file hợp lệ thành công và file nguy hiểm bị từ chối.
- [ ] Delete media đang dùng có cảnh báo/policy.
- [ ] Variant chạy queue và failure được ghi.
- [ ] API typed và permission test.
- [ ] UI tối thiểu chọn được media cho module sau.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
