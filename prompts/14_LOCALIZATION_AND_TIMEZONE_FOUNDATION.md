# PROMPT 14 — ĐA NGÔN NGỮ VÀ TIMEZONE

**Phase:** 02 — Core CMS  
**Flag:** `REQUIRED`

## Mục tiêu

Thiết lập tiếng Việt mặc định, tiếng Anh sẵn sàng bật, translation-table conventions và hiển thị giờ Việt Nam.

## Điều kiện tiên quyết

1. P13 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P14 — Đa ngôn ngữ và timezone
PHẠM VI: 02 — Core CMS
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P14.
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
Thiết lập tiếng Việt mặc định, tiếng Anh sẵn sàng bật, translation-table conventions và hiển thị giờ Việt Nam.

NHIỆM VỤ BẮT BUỘC:
1. Tạo/hoàn thiện `hongvan_languages`, active/default/fallback và locale validation.
2. Định nghĩa interface/trait cho translatable entity với translation tables; không nhét mọi nội dung vào JSON.
3. Thiết lập admin locale switch và public locale middleware/route strategy theo ADR.
4. Tiếng Việt mặc định; English có thể disabled nhưng schema và UI hỗ trợ.
5. Slug uniqueness theo locale và namespace.
6. DB timestamps UTC; API ISO8601; UI hiển thị Asia/Ho_Chi_Minh hoặc user preference nếu mở rộng.
7. Translate validation/API labels và admin core labels cần thiết.
8. Tạo missing-translation fallback và report, không tự ghi DB khi public request.
9. Test locale routing, fallback, slug conflict và timezone conversion.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] VI hoạt động đầy đủ.
- [ ] EN disabled không tạo broken route.
- [ ] Không trộn translation JSON với bảng nếu cần query.
- [ ] Timezone conversion có test boundary.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Locale`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P14.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 14 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P15.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P15.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] VI hoạt động đầy đủ.
- [ ] EN disabled không tạo broken route.
- [ ] Không trộn translation JSON với bảng nếu cần query.
- [ ] Timezone conversion có test boundary.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
