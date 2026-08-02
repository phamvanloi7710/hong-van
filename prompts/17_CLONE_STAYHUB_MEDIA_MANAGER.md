# PROMPT 17 — CLONE MEDIA MANAGER TỪ STAYHUB

**Phase:** 03 — Media & Frontend  
**Flag:** `DEFERRED_ALLOWED`

## Mục tiêu

Clone chính xác chức năng và trải nghiệm trang media tham chiếu vào Hồng Vân, trên domain/API an toàn đã có.

## Điều kiện tiên quyết

1. P16 DONE.
2. Gate StayHub Media = READY. Nếu source thiếu: cập nhật DEFERRED và dừng prompt này, không fail prompt khác.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P17 — Clone Media Manager từ StayHub
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: DEFERRED_ALLOWED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P17.
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
Clone chính xác chức năng và trải nghiệm trang media tham chiếu vào Hồng Vân, trên domain/API an toàn đã có.

NHIỆM VỤ BẮT BUỘC:
1. Đọc inventory và source thật trong `SourceIntegrations/StayHubMedia/`; xác minh không có thay đổi kể từ P01.
2. Tạo feature parity matrix theo `docs/MEDIA_CLONE_CHECKLIST.md`, đánh dấu exact/adapted/not-applicable với lý do.
3. Port layout, toolbar, folder tree, grid/list, breadcrumb, dialogs, selection, upload progress, bulk actions, metadata panel, preview, empty/loading/error states.
4. Map API/source behavior vào API Hồng Vân; không copy hardcode tenant/domain/token.
5. Giữ style admin template Hồng Vân khi source StayHub dùng style khác, nhưng clone luồng và khả năng; nếu yêu cầu visual exact thì ghi quyết định rõ.
6. Tạo keyboard/accessibility behavior có trong source.
7. Tạo used-by, trash/restore, replace/crop/resize nếu source có.
8. Tạo E2E và visual regression theo các màn hình chính.
9. Cập nhật inventory/matrix và trạng thái gate.

KHÔNG ĐƯỢC:
- Không tự dựng theo URL nếu source thiếu.
- Không bê code có license không cho phép.

TIÊU CHÍ NGHIỆM THU:
- [ ] Source read-only không có diff.
- [ ] Parity matrix có bằng chứng.
- [ ] Luồng upload/search/folder/select/trash/restore hoạt động.
- [ ] Permission backend bảo vệ mọi action.
- [ ] Không còn label/domain StayHub ngoài tài liệu attribution/mapping.

KIỂM TRA TỐI THIỂU:
- `git diff -- SourceIntegrations/StayHubMedia`
- `cd BackEnd && php artisan test --filter=Media`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build`
- `cd Admin && npx playwright test media`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P17.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 17 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P18.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P18.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Source read-only không có diff.
- [ ] Parity matrix có bằng chứng.
- [ ] Luồng upload/search/folder/select/trash/restore hoạt động.
- [ ] Permission backend bảo vệ mọi action.
- [ ] Không còn label/domain StayHub ngoài tài liệu attribution/mapping.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
