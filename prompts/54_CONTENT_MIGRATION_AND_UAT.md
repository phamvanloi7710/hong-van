# PROMPT 54 — NHẬP NỘI DUNG VÀ UAT

**Phase:** 09 — Launch  
**Flag:** `REQUIRED`

## Mục tiêu

Đưa nội dung thật vào staging, kiểm thử nghiệp vụ/visual/SEO với đại diện người dùng.

## Điều kiện tiên quyết

1. P53 DONE.
2. Content/company data được cung cấp; external source gates production-ready hoặc có acceptance.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P54 — Nhập nội dung và UAT
PHẠM VI: 09 — Launch
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P54.
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
Đưa nội dung thật vào staging, kiểm thử nghiệp vụ/visual/SEO với đại diện người dùng.

NHIỆM VỤ BẮT BUỘC:
1. Lập content inventory thật: company/legal/contact, products, services, fleet, warehouses, posts, projects, partners, certifications, media.
2. Tạo import templates/commands idempotent với dry-run, validation report, mapping media/slug/locale.
3. Không tự bịa dữ liệu còn thiếu; tạo issue/checklist.
4. Import staging, review page builder layouts, header/footer, navigation, responsive.
5. UAT scripts theo role và public journeys.
6. Review spelling Vietnamese, price/contact display, forms/notifications, privacy, SEO tags/schema/sitemap.
7. Log UAT defects với severity/owner/status; fix và retest.
8. Freeze migration mapping/version trước production.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] UAT sign-off hoặc blocker list.
- [ ] No demo/fake data còn trên production dataset.
- [ ] Import dry-run/re-run an toàn.
- [ ] Forms gửi đúng team.
- [ ] Source gates không còn silent deferred.

KIỂM TRA TỐI THIỂU:
- `run import --dry-run`
- `run staging import`
- `full smoke/E2E`
- `UAT checklist`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P54.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 54 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P55.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P55.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] UAT sign-off hoặc blocker list.
- [ ] No demo/fake data còn trên production dataset.
- [ ] Import dry-run/re-run an toàn.
- [ ] Forms gửi đúng team.
- [ ] Source gates không còn silent deferred.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.
