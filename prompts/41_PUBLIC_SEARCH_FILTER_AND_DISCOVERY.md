# PROMPT 41 — TÌM KIẾM VÀ KHÁM PHÁ NỘI DUNG PUBLIC

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo search nội bộ, filters và related discovery không làm lộ draft hoặc tạo query nguy hiểm.

## Điều kiện tiên quyết

1. P33–P40 core modules DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P41 — Tìm kiếm và khám phá nội dung public
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P41.
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
Tạo search nội bộ, filters và related discovery không làm lộ draft hoặc tạo query nguy hiểm.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa search scope: products, crop solutions, services, posts, projects/pages; chỉ published và active locale.
2. Chọn MySQL full-text hoặc Scout driver dựa trên evidence/scale; ghi ADR, không over-engineer.
3. Tạo query normalization tiếng Việt, pagination, type filters và highlight an toàn.
4. Log search terms đã giảm dữ liệu cá nhân vào `hongvan_search_logs` nếu analytics enabled.
5. Public search Blade SSR và Page Builder search block nếu cần.
6. Admin reindex command/health.
7. Related content dựa taxonomy explicit, không AI giả.
8. Rate limit và query length.
9. Test draft exclusion, SQL/sort injection, accents, empty/no-result.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Search không lộ draft.
- [ ] Tiếng Việt tìm hợp lý theo giải pháp đã chọn.
- [ ] Không raw query injection.
- [ ] Performance có index/explain baseline.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Search`
- `cd BackEnd && php artisan search:reindex hoặc command tương ứng ở test/staging`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P41.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 41 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P42.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P42.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Search không lộ draft.
- [ ] Tiếng Việt tìm hợp lý theo giải pháp đã chọn.
- [ ] Không raw query injection.
- [ ] Performance có index/explain baseline.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
