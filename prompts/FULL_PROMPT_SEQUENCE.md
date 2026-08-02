# FULL PROMPT SEQUENCE — HỒNG VÂN V2

Toàn bộ 57 prompt P00–P56. Source giao diện website public được đặt tại `FrontEndTemplate/`.

Khuyến nghị: Codex đọc file prompt riêng; không chạy toàn bộ tài liệu này trong một lượt.

---

# PROMPT 00 — THIẾT LẬP BASELINE VÀ KIỂM KÊ REPOSITORY

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định.

## Điều kiện tiên quyết

1. Bộ prompt đã được giải nén tại root project.
2. Codex được mở ở đúng root có `AGENTS.md`.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P00 — Thiết lập baseline và kiểm kê repository
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P00.
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
Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định.

NHIỆM VỤ BẮT BUỘC:
1. Đọc `AGENTS.md`, `START_HERE.md`, `docs/PROJECT_CHARTER.md`, `docs/TECH_STACK_LOCK.md`, `docs/CODEX_WORKFLOW.md` và `docs/CODEX_STATE.md`.
2. Chạy `git status` và ghi nhận repository đã init hay chưa, branch hiện tại, file untracked/modified; tuyệt đối không xóa thay đổi người dùng.
3. Kiểm kê ở mức top-level: `BackEnd/`, `Admin/`, `Template/`, `FrontEndTemplate/`, `SourceIntegrations/StayHubMedia/`, `prompts/`, `docs/`.
4. Xác định hệ điều hành/shell, PHP, Composer, Node, npm, Git, MySQL client và Docker hiện có. Chỉ ghi version; không tự nâng cấp.
5. Xác minh tên công ty và phạm vi: catalog phân bón + vận chuyển + kho bãi + CMS/Page Builder + lead; không e-commerce.
6. Đánh dấu source gate ban đầu: template admin có/không; frontend template có/không; StayHub media source có/không.
7. Cập nhật `docs/CODEX_STATE.md` bằng dữ liệu thật, không sửa các quyết định kiến trúc.
8. Tạo báo cáo baseline ngắn tại `docs/reports/P00_BASELINE.md` gồm môi trường, source gates, blocker và đề xuất prompt kế tiếp.

KHÔNG ĐƯỢC:
- Không cài framework.
- Không sửa Template/FrontEndTemplate/SourceIntegrations.
- Không chạy prompt 01.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có file source nào bị chỉnh ngoài `docs/CODEX_STATE.md` và báo cáo P00.
- [ ] Trạng thái Git và công cụ được ghi đúng từ lệnh thực tế.
- [ ] Ba external source gate có trạng thái rõ: READY, MISSING hoặc INVALID.
- [ ] `docs/CODEX_STATE.md` đặt `last_completed_prompt: 00` và `next_prompt: 01_EXTERNAL_SOURCE_INVENTORY`.

KIỂM TRA TỐI THIỂU:
- `git status --short --branch`
- `php -v`
- `composer --version`
- `node --version`
- `npm --version`
- `git --version`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P00.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 00 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P01.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P01.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có file source nào bị chỉnh ngoài `docs/CODEX_STATE.md` và báo cáo P00.
- [ ] Trạng thái Git và công cụ được ghi đúng từ lệnh thực tế.
- [ ] Ba external source gate có trạng thái rõ: READY, MISSING hoặc INVALID.
- [ ] `docs/CODEX_STATE.md` đặt `last_completed_prompt: 00` và `next_prompt: 01_EXTERNAL_SOURCE_INVENTORY`.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 01 — KIỂM KÊ TEMPLATE VÀ EXTERNAL SOURCE

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source.

## Điều kiện tiên quyết

1. Prompt 00 DONE.
2. `docs/CODEX_STATE.md` phản ánh source gates.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P01 — Kiểm kê template và external source
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P01.
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
Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source.

NHIỆM VỤ BẮT BUỘC:
1. Đọc root và các `AGENTS.md` trong `Template/`, `FrontEndTemplate/`, `SourceIntegrations/`.
2. Nếu `Template/` có source: tìm `package.json`, `angular.json`, entry points, layout, routing, menu, theme settings, icon/font/assets, auth screens và build commands; ghi version/dependency bằng cách đọc manifest, không cài.
3. Tạo `docs/inventories/ADMIN_TEMPLATE_INVENTORY.md` với cấu trúc, thành phần tái sử dụng, rủi ro nâng lên Angular 22 và license note.
4. Nếu `FrontEndTemplate/` có source: inventory page layouts, CSS system, JS plugins, components/sections, breakpoints, typography, header/footer và asset; ghi vào `docs/inventories/FRONTEND_TEMPLATE_INVENTORY.md`.
5. Nếu StayHub media source có mặt: inventory route, components, APIs, models, permissions, storage và behaviors; ghi vào `docs/inventories/STAYHUB_MEDIA_INVENTORY.md`.
6. Nếu một source thiếu, tạo inventory file ghi `DEFERRED — SOURCE MISSING`, đúng path cần bổ sung và không mô tả giả chức năng.
7. Tạo `docs/inventories/SOURCE_MAPPING_SUMMARY.md` nêu thứ gì port, thứ gì bỏ, thứ gì cần quyết định.
8. Cập nhật source gate trong `docs/CODEX_STATE.md` và task ledger.

KHÔNG ĐƯỢC:
- Không npm install/composer install trong source tham chiếu.
- Không copy source vào app ở bước này.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có thay đổi bên trong source read-only.
- [ ] Inventory dùng bằng chứng từ manifest/file thật.
- [ ] Source thiếu được đánh dấu deferred, không bị báo lỗi toàn project.
- [ ] Có mapping rõ giữa source và `Admin/`/`BackEnd/resources/`/Media domain.

KIỂM TRA TỐI THIỂU:
- `git diff -- Template FrontEndTemplate SourceIntegrations`
- `git status --short`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P01.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 01 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P02.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P02.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có thay đổi bên trong source read-only.
- [ ] Inventory dùng bằng chứng từ manifest/file thật.
- [ ] Source thiếu được đánh dấu deferred, không bị báo lỗi toàn project.
- [ ] Có mapping rõ giữa source và `Admin/`/`BackEnd/resources/`/Media domain.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 02 — CHỐT ADR, MODULE MAP VÀ KẾ HOẠCH BÀN GIAO

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng.

## Điều kiện tiên quyết

1. P00–P01 DONE hoặc source gate đã được đánh dấu deferred.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P02 — Chốt ADR, module map và kế hoạch bàn giao
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P02.
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
Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng.

NHIỆM VỤ BẮT BUỘC:
1. Rà `docs/ARCHITECTURE.md`, `DATABASE_BLUEPRINT.md`, `PAGE_BUILDER_CONTRACT.md`, `API_CONVENTIONS.md`, `SECURITY_BASELINE.md`.
2. Tạo ADR riêng trong `docs/adr/` cho: monorepo, Laravel Blade public, Angular admin, Sanctum same-origin, explicit table prefix, Blade iframe preview, external-source read-only, no e-commerce.
3. Tạo `docs/MODULE_MAP.md` mô tả bounded context, owner data, public routes, admin routes, permissions và dependency.
4. Tạo `docs/DELIVERY_PHASES.md` nhóm 57 prompt thành milestones và source gates.
5. Tạo `docs/NON_FUNCTIONAL_REQUIREMENTS.md`: security, accessibility, performance, SEO, observability, backup, browser support, file upload limits configurable.
6. Định nghĩa Definition of Done dùng chung: code + migration + auth + test + docs + lint/build + state.
7. Không thay đổi kiến trúc đã accepted nếu chưa có mâu thuẫn; nếu có, tạo ADR `Proposed` thay vì âm thầm đổi.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Mỗi ADR có Context, Decision, Consequences, Status, Date.
- [ ] Module map không biến website thành ERP/WMS/TMS/e-commerce.
- [ ] Definition of Done áp dụng được cho backend và Angular.
- [ ] Không có thông tin công ty giả.

KIỂM TRA TỐI THIỂU:
- `find docs/adr -maxdepth 1 -type f`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P02.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 02 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P03.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P03.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Mỗi ADR có Context, Decision, Consequences, Status, Date.
- [ ] Module map không biến website thành ERP/WMS/TMS/e-commerce.
- [ ] Definition of Done áp dụng được cho backend và Angular.
- [ ] Không có thông tin công ty giả.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 03 — THIẾT LẬP REPOSITORY HYGIENE VÀ BASELINE CÔNG CỤ

**Phase:** 00 — Governance  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework.

## Điều kiện tiên quyết

1. P02 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P03 — Thiết lập repository hygiene và baseline công cụ
PHẠM VI: 00 — Governance
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P03.
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
Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework.

NHIỆM VỤ BẮT BUỘC:
1. Kiểm tra `.editorconfig`, `.gitattributes`, `.gitignore` có bảo vệ source template/license, env, vendor, node_modules, build output và logs.
2. Tạo `.env.example` ở root chỉ chứa biến mô tả path/build chung; không chứa secret.
3. Tạo `docs/LOCAL_DEVELOPMENT.md` cho Windows và Linux, nhưng chưa giả định framework đã được cài.
4. Tạo `scripts/verify-prerequisites.ps1` và `.sh` để kiểm tra version PHP/Composer/Node/npm/Git và cảnh báo không tương thích; không tự cài.
5. Tạo `scripts/verify-readonly-sources.ps1` và `.sh` ghi hash/status để phát hiện source tham chiếu bị sửa.
6. Thiết lập commit message convention và branch convention trong `docs/GIT_WORKFLOW.md`.
7. Nếu Git chưa init, chỉ init khi working directory an toàn; không tự thêm remote.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Scripts fail-fast, không in secret, hỗ trợ path có khoảng trắng.
- [ ] Ignore không vô tình ignore `AGENTS.md` hoặc prompt/docs.
- [ ] Không commit build output hoặc source template theo mặc định.
- [ ] Baseline scripts chạy được trên shell hiện tại hoặc ghi rõ chưa test shell khác.

KIỂM TRA TỐI THIỂU:
- `git check-ignore -v Template/README_PLACE_ADMIN_TEMPLATE_HERE.md || true`
- `git diff --check`
- `scripts/verify-prerequisites.* (chạy bản phù hợp hệ điều hành)`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P03.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 03 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P04.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P04.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Scripts fail-fast, không in secret, hỗ trợ path có khoảng trắng.
- [ ] Ignore không vô tình ignore `AGENTS.md` hoặc prompt/docs.
- [ ] Không commit build output hoặc source template theo mặc định.
- [ ] Baseline scripts chạy được trên shell hiện tại hoặc ghi rõ chưa test shell khác.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 05 — KHỞI TẠO ANGULAR 22.1 ADMIN

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready.

## Điều kiện tiên quyết

1. P04 DONE.
2. Node/npm tương thích Angular 22.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P05 — Khởi tạo Angular 22.1 admin
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P05.
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
Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready.

NHIỆM VỤ BẮT BUỘC:
1. Xác nhận Angular 22.1 stable patch và compatibility Node/TypeScript.
2. Vì `Admin/` không rỗng, chạy Angular CLI trong `.bootstrap/Admin` rồi merge an toàn vào `Admin/`, giữ AGENTS hiện có.
3. Khởi tạo standalone, routing, strict TypeScript, SCSS hoặc preprocessor đúng template; không thêm SSR cho admin.
4. Đặt project name `hongvan-admin`, prefix selector `hv` hoặc tên nhất quán đã ghi ADR.
5. Tạo khung `core/`, `shared/`, `features/` và lazy route placeholder; không dựng business UI.
6. Cấu hình environment typed cho API base `/api/admin/v1`, app base `/admin/`; không hardcode domain.
7. Thiết lập npm scripts `lint`, `test`, `build`, `build:laravel` placeholder có fail rõ cho bước P07.
8. Giữ lockfile và engine requirement.
9. Xóa `.bootstrap/Admin` sau merge thành công.

KHÔNG ĐƯỢC:
- Không port template admin ở bước này.
- Không cài state library lớn khi chưa có nhu cầu.

TIÊU CHÍ NGHIỆM THU:
- [ ] `ng version` báo Angular/CLI 22.1.x cùng dòng.
- [ ] `npm ci`, test và build mặc định pass.
- [ ] Strict mode bật.
- [ ] Không dùng NgModule architecture cũ nếu CLI không cần.
- [ ] AGENTS ở feature vẫn còn.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm ci`
- `cd Admin && npx ng version`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P05.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 05 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P06.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P06.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] `ng version` báo Angular/CLI 22.1.x cùng dòng.
- [ ] `npm ci`, test và build mặc định pass.
- [ ] Strict mode bật.
- [ ] Không dùng NgModule architecture cũ nếu CLI không cần.
- [ ] AGENTS ở feature vẫn còn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 06 — PORT TEMPLATE ADMIN VÀO ANGULAR 22

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu.

## Điều kiện tiên quyết

1. P05 DONE.
2. Gate Admin Template = READY; nếu thiếu thì BLOCKED, không deferred toàn project.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P06 — Port template admin vào Angular 22
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P06.
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
Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu.

NHIỆM VỤ BẮT BUỘC:
1. Đọc inventory P01 và kiểm tra lại manifest/template source có thay đổi.
2. Lập mapping cụ thể: app shell, header, sidebar, menu, breadcrumb, footer, auth layout, typography, icon, assets, theme service, demo pages.
3. Nếu template khác Angular 22, port từng layout/component vào Angular 22 thay vì nâng/sửa source trong `Template/`.
4. Giữ visual fidelity: spacing, breakpoints, menus, animations cần thiết và responsive states.
5. Loại bỏ demo business pages không dùng nhưng giữ component showcase cần để đối chiếu.
6. Tách `Admin/src/app/core/layout` và shared components đúng ranh giới.
7. Port theme settings UI của template nhưng chưa lưu server; dùng adapter local tạm với contract sẽ thay ở P12.
8. Chuẩn hóa asset import và license notices.
9. Tạo route `/admin` shell, `/admin/login` auth shell và dashboard placeholder.
10. Tạo visual checklist/screenshots nội bộ nếu tool có sẵn; không dùng screenshot làm source duy nhất.

KHÔNG ĐƯỢC:
- Không thay template bằng UI khác.
- Không giữ hardcode demo credentials/domain.

TIÊU CHÍ NGHIỆM THU:
- [ ] Source `Template/` có diff bằng 0.
- [ ] Admin build pass Angular 22.
- [ ] Layout desktop/mobile tương đồng template.
- [ ] Theme panel cơ bản hoạt động tạm.
- [ ] Không kéo theo API/demo backend hoặc secret của template.

KIỂM TRA TỐI THIỂU:
- `git diff -- Template`
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P06.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 06 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P07.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P07.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Source `Template/` có diff bằng 0.
- [ ] Admin build pass Angular 22.
- [ ] Layout desktop/mobile tương đồng template.
- [ ] Theme panel cơ bản hoạt động tạm.
- [ ] Không kéo theo API/demo backend hoặc secret của template.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 07 — TÍCH HỢP BUILD ANGULAR VÀO LARAVEL

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`.

## Điều kiện tiên quyết

1. P06 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P07 — Tích hợp build Angular vào Laravel
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P07.
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
Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`.

NHIỆM VỤ BẮT BUỘC:
1. Kiểm tra Angular builder output thực tế của v22.1; không giả định cấu trúc cũ.
2. Cấu hình base href/deploy path `/admin/` và production environment `/api/admin/v1`.
3. Tạo `Admin/tools/sync-to-laravel` đa nền tảng hoặc npm script để xóa có guard đúng output cũ rồi copy build mới vào `BackEnd/public/admin/browser/`.
4. Tạo `scripts/build-admin.ps1` và `.sh` gọi `npm ci` khi cần, lint/test tùy mode, build production và sync.
5. Thêm Laravel/Nginx-friendly fallback route hoặc response file cho `/admin/{path?}` mà không nuốt `/api`, `/preview`, static assets hoặc public routes.
6. Thêm cache headers: index không cache dài; hashed assets cache immutable.
7. Tạo smoke test xác nhận `/admin/` trả index và asset path hợp lệ sau build.
8. Document quy trình trong `docs/LOCAL_DEVELOPMENT.md`.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] `npm run build:laravel` tạo đúng output.
- [ ] Không chỉnh thủ công output build.
- [ ] Refresh một deep link admin không 404.
- [ ] Public Laravel route không bị admin catch-all chiếm.
- [ ] Source map production theo policy.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run build:laravel`
- `cd BackEnd && php artisan test --filter=AdminSpa`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P07.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 07 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P08.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P08.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] `npm run build:laravel` tạo đúng output.
- [ ] Không chỉnh thủ công output build.
- [ ] Refresh một deep link admin không 404.
- [ ] Public Laravel route không bị admin catch-all chiếm.
- [ ] Source map production theo policy.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 08 — XÂY NỀN DATABASE VÀ CƯỠNG CHẾ TIỀN TỐ

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix.

## Điều kiện tiên quyết

1. P04 DONE.
2. MySQL test database sẵn sàng hoặc SQLite không được dùng để che khác biệt MySQL.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P08 — Xây nền database và cưỡng chế tiền tố
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P08.
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
Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix.

NHIỆM VỤ BẮT BUỘC:
1. Rà migration mặc định Laravel 13 và đổi tên mọi bảng: users, password reset, sessions, cache, jobs, failed jobs, batches, notifications, Sanctum, migrations registry khi cấu hình cho phép.
2. Không dùng connection-level prefix.
3. Tạo base contracts/traits cho `public_id`, audit stamps, sortable status nếu có lợi; không tạo abstraction chung chung.
4. Thiết lập database charset/collation hỗ trợ tiếng Việt và emoji phù hợp MySQL 8.4.
5. Chọn convention: internal bigint id + public ULID; ghi ADR.
6. Tạo `scripts/check-table-prefix.php` scan migrations, model table names và known config; exit non-zero khi phát hiện tên sai.
7. Tạo architecture test cho prefix.
8. Tạo migration foundation cho `hongvan_languages`, `hongvan_setting_groups`, `hongvan_settings` nếu thuộc core bắt buộc; chưa thêm business tables.
9. Kiểm tra migrate fresh, rollback batch và migrate lại.
10. Cập nhật DATABASE_BLUEPRINT bằng schema thực nếu khác.

KHÔNG ĐƯỢC:
- Không thêm bảng business ngoài scope.
- Không dùng DB_PREFIX connection config.

TIÊU CHÍ NGHIỆM THU:
- [ ] Database fresh chỉ có bảng `hongvan_*` do project tạo.
- [ ] Không double-prefix.
- [ ] Script prefix bắt được fixture sai trong test.
- [ ] Migrations rollback sạch.
- [ ] Model/core config trỏ đúng bảng.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --env=testing`
- `cd BackEnd && php artisan test --filter=TablePrefix`
- `cd BackEnd && php ../scripts/check-table-prefix.php`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P08.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 08 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P09.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P09.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Database fresh chỉ có bảng `hongvan_*` do project tạo.
- [ ] Không double-prefix.
- [ ] Script prefix bắt được fixture sai trong test.
- [ ] Migrations rollback sạch.
- [ ] Model/core config trỏ đúng bảng.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 09 — XÂY NỀN API ADMIN V1

**Phase:** 01 — Foundation  
**Flag:** `REQUIRED`

## Mục tiêu

Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module.

## Điều kiện tiên quyết

1. P08 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P09 — Xây nền API admin v1
PHẠM VI: 01 — Foundation
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P09.
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
Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module.

NHIỆM VỤ BẮT BUỘC:
1. Tạo route namespace `/api/admin/v1` và public `/api/public/v1` tối thiểu.
2. Triển khai response envelope đúng `docs/API_CONVENTIONS.md` bằng Resource/response factory vừa đủ, không bọc file download/stream sai cách.
3. Tạo request ID middleware và log context, không lộ trace.
4. Chuẩn hóa validation exception, authorization exception, not found, conflict và rate-limit response.
5. Tạo pagination metadata.
6. Tạo typed filter/sort allowlist helpers; cấm client truyền raw column.
7. Thiết lập API locale từ user/request với allowlist.
8. Tạo `/api/admin/v1/system/ping` protected placeholder hoặc public health riêng theo security.
9. Viết feature tests cho success, validation, 404 và unexpected exception production behavior.
10. Tạo OpenAPI strategy document; chưa cần sinh toàn bộ spec.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Response contract nhất quán.
- [ ] Status code đúng.
- [ ] Production response không lộ stack trace.
- [ ] Sort injection bị từ chối.
- [ ] Request ID xuất hiện trong response/log context.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Api`
- `cd BackEnd && vendor/bin/pint --test`
- `cd BackEnd && vendor/bin/phpstan analyse app/Http app/Support`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P09.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 09 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P10.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P10.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Response contract nhất quán.
- [ ] Status code đúng.
- [ ] Production response không lộ stack trace.
- [ ] Sort injection bị từ chối.
- [ ] Request ID xuất hiện trong response/log context.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 10 — XÁC THỰC ADMIN BẰNG SANCTUM COOKIE/SESSION

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin.

## Điều kiện tiên quyết

1. P07–P09 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P10 — Xác thực admin bằng Sanctum cookie/session
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P10.
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
Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin.

NHIỆM VỤ BẮT BUỘC:
1. Cài/cấu hình Sanctum phiên bản tương thích Laravel 13; publish migration và đổi bảng thành `hongvan_personal_access_tokens` dù admin chính dùng cookie.
2. Cấu hình stateful domains từ env, CSRF cookie, session cookie name có prefix Hồng Vân, SameSite/Secure/HttpOnly đúng môi trường.
3. Tạo API login/logout/me/forgot-password/reset-password với Form Requests và rate limits.
4. Regenerate session sau login; invalidate đúng sau logout/password reset.
5. Không lưu bearer token trong localStorage cho flow admin same-origin.
6. Tạo Angular auth service, session bootstrap, login form dùng đúng template, auth guard và interceptor CSRF/401.
7. Thêm loading/error state, không phân biệt email tồn tại ở forgot-password.
8. Thiết lập account active/locked fields và kiểm tra login.
9. Audit login success/failure ở mức không lộ password.
10. Viết backend tests và Angular tests.

KHÔNG ĐƯỢC:
- Không tắt CSRF.
- Không chuyển sang JWT chỉ để đơn giản.

TIÊU CHÍ NGHIỆM THU:
- [ ] Login Angular hoạt động bằng cookie + CSRF.
- [ ] Refresh giữ session hợp lệ.
- [ ] Logout vô hiệu session.
- [ ] Inactive/locked user bị chặn.
- [ ] Không có token nhạy cảm trong localStorage/log.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Auth`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P10.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 10 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P11.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P11.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Login Angular hoạt động bằng cookie + CSRF.
- [ ] Refresh giữ session hợp lệ.
- [ ] Logout vô hiệu session.
- [ ] Inactive/locked user bị chặn.
- [ ] Không có token nhạy cảm trong localStorage/log.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 11 — QUẢN LÝ NGƯỜI DÙNG, VAI TRÒ VÀ QUYỀN

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default.

## Điều kiện tiên quyết

1. P10 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P11 — Quản lý người dùng, vai trò và quyền
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P11.
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
Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/models cho `hongvan_roles`, `hongvan_permissions`, pivots và optional user overrides; tất cả prefix.
2. Định nghĩa permission namespace theo module và action: view, create, update, delete, restore, publish, export, manage_settings.
3. Seed Super Admin an toàn bằng env/command, không hardcode password trong source.
4. Tạo policies/gates và middleware; Super Admin bypass phải explicit, audit và test.
5. Tạo CRUD API users/roles/permissions, pagination/filter, activate/lock, reset sessions.
6. Tạo Angular feature identity với routes lazy, tables/forms/dialogs theo template.
7. Tạo permission guard và structural directive/utility để ẩn/disable UI, nhưng backend vẫn là nguồn chân lý.
8. Ngăn user tự gỡ role cuối cùng có quyền quản trị hoặc xóa chính mình theo policy rõ.
9. Audit thay đổi role/permission/user.
10. Tạo permission matrix tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] User không quyền nhận 403 dù gọi API trực tiếp.
- [ ] UI phản ánh quyền sau refresh.
- [ ] Không thể làm mất Super Admin cuối cùng.
- [ ] Tất cả bảng prefix.
- [ ] Permission seed idempotent.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --seed --env=testing`
- `cd BackEnd && php artisan test --filter=Permission`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P11.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 11 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P12.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P12.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] User không quyền nhận 403 dù gọi API trực tiếp.
- [ ] UI phản ánh quyền sau refresh.
- [ ] Không thể làm mất Super Admin cuối cùng.
- [ ] Tất cả bảng prefix.
- [ ] Permission seed idempotent.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 12 — LƯU THEME ADMIN THEO TỪNG USER

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn.

## Điều kiện tiên quyết

1. P06, P10 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P12 — Lưu theme admin theo từng user
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P12.
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
Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_user_preferences` với namespace/key hoặc typed columns phù hợp; có unique user+namespace.
2. Định nghĩa schema allowlist cho mode, skin, menu style, compact, direction, density, allowed color tokens và các option thật sự có trong template.
3. Tạo API get/update/reset preference; validation server-side.
4. Angular bootstrap theme trước khi paint nếu có thể để giảm flash, nhưng không chặn app vô hạn.
5. Cache local chỉ là optimization; server là nguồn chân lý.
6. Merge order: template default → system default → user preference.
7. Handle preference cũ/invalid khi template update.
8. Tạo UI theme panel đúng template, nút reset và preview.
9. Audit thay đổi quan trọng nếu ảnh hưởng accessibility.
10. Test hai user có theme khác nhau và không rò preference.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Theme tồn tại qua logout/login và thiết bị khác.
- [ ] User A không đọc/sửa user B.
- [ ] Invalid token bị reject.
- [ ] Fallback hoạt động khi preference lỗi.
- [ ] Build pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=UserPreference`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P12.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 12 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P13.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P13.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Theme tồn tại qua logout/login và thiết bị khác.
- [ ] User A không đọc/sửa user B.
- [ ] Invalid token bị reject.
- [ ] Fallback hoạt động khi preference lỗi.
- [ ] Build pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 13 — THIẾT LẬP THÔNG TIN CÔNG TY VÀ CẤU HÌNH TOÀN CỤC

**Phase:** 02 — Core CMS  
**Flag:** `REQUIRED`

## Mục tiêu

Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp.

## Điều kiện tiên quyết

1. P11 DONE.
2. Core settings tables P08 tồn tại.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P13 — Thiết lập thông tin công ty và cấu hình toàn cục
PHẠM VI: 02 — Core CMS
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P13.
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
Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế setting groups: company, legal, contact, social, branding, business hours, map, quote, email, SEO defaults, feature flags.
2. Tạo branches, business hours, social links, contact channels với order/status.
3. Secret settings phải dùng encrypted storage hoặc env reference; không trả plain secret về Angular.
4. Tạo typed settings service và cache với invalidation.
5. Tạo admin forms theo group, validation và permission `settings.*`.
6. Logo/favicon/OG default chọn từ Media contract, có thể tạm null trước P16.
7. Không seed địa chỉ, MST, hotline giả; chỉ seed tên pháp lý và locale/timezone khi đã xác nhận.
8. Tạo public helper/view model để Blade dùng settings mà không query lặp.
9. Audit changes và redaction.
10. Test cache invalidation, permission và secret masking.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Thông tin công ty chỉnh được từ admin.
- [ ] Không hardcode contact trong Blade/Angular.
- [ ] Secret không lộ qua API/log.
- [ ] Cache cập nhật ngay sau save.
- [ ] Data chưa có để trống có validation phù hợp.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Settings`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P13.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 13 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P14.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P14.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Thông tin công ty chỉnh được từ admin.
- [ ] Không hardcode contact trong Blade/Angular.
- [ ] Secret không lộ qua API/log.
- [ ] Cache cập nhật ngay sau save.
- [ ] Data chưa có để trống có validation phù hợp.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 15 — NHẬT KÝ HOẠT ĐỘNG VÀ HARDENING NỀN

**Phase:** 02 — Identity & Security  
**Flag:** `REQUIRED`

## Mục tiêu

Xây audit trail, security headers, rate limiting và redaction trước các module nội dung.

## Điều kiện tiên quyết

1. P10–P14 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P15 — Nhật ký hoạt động và hardening nền
PHẠM VI: 02 — Identity & Security
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P15.
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
Xây audit trail, security headers, rate limiting và redaction trước các module nội dung.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_audit_logs` append-only: actor, action, subject type/public id, before/after redacted diff, IP/user agent hash/metadata, request ID, timestamp.
2. Tạo audit service/event subscriber cho auth, identity, settings và chuẩn cho module sau.
3. Không audit password/token/cookie/body file hoặc secret plain.
4. Tạo API/UI xem audit theo permission, filter allowlist, không sửa/xóa thông thường.
5. Thiết lập CSP, X-Content-Type-Options, Referrer-Policy, HSTS production, frame rules cho admin/preview có ngoại lệ tối thiểu.
6. Rate limit login, public forms placeholder, upload, preview session.
7. Thiết lập trusted proxies/hosts từ env.
8. Tạo security logging channel và retention config.
9. Test audit integrity, redaction, permission và headers.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Thao tác nhạy cảm tạo audit.
- [ ] Audit không chứa secret.
- [ ] Admin thường không sửa/xóa log.
- [ ] Preview iframe vẫn hoạt động theo frame/CSP design.
- [ ] Headers test pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Audit`
- `cd BackEnd && php artisan test --filter=SecurityHeaders`
- `cd BackEnd && vendor/bin/pint --test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P15.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 15 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P16.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P16.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Thao tác nhạy cảm tạo audit.
- [ ] Audit không chứa secret.
- [ ] Admin thường không sửa/xóa log.
- [ ] Preview iframe vẫn hoạt động theo frame/CSP design.
- [ ] Headers test pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

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


---

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


---

# PROMPT 19 — PORT FRONTEND TEMPLATE PUBLIC VÀO LARAVEL BLADE

**Phase:** 03 — Media & Frontend  
**Flag:** `DEFERRED_ALLOWED`

## Mục tiêu

Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder.

## Điều kiện tiên quyết

1. P18 DONE.
2. Gate FrontEndTemplate = READY. Nếu source thiếu: DEFERRED và dừng prompt này.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P19 — Port frontend template public vào Blade
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: DEFERRED_ALLOWED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P19.
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
Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder.

NHIỆM VỤ BẮT BUỘC:
1. Đọc inventory/source `FrontEndTemplate/`, xác định layouts/pages/sections/assets/plugins/license.
2. Tách typography, colors, spacing, containers, breakpoints, buttons, forms, cards thành token/component; tránh copy CSS không kiểm soát.
3. Port header/footer/navigation, home sections, listing/detail templates, contact và content layouts sang Blade.
4. Thay asset/path hardcode bằng Vite/Media/settings helpers.
5. Loại bỏ plugin JS không cần; thay bằng giải pháp nhẹ, accessible khi có thể.
6. Không thay đổi source tham chiếu.
7. Tạo mapping `template section → Page Builder block type` trong docs.
8. Visual compare desktop/tablet/mobile và sửa chênh lệch có chủ đích.
9. Giữ nội dung demo ngoài seed, không hardcode vào view.

KHÔNG ĐƯỢC:
- Không chạy nguyên template tĩnh trong production.
- Không sửa source tham chiếu.

TIÊU CHÍ NGHIỆM THU:
- [ ] Source FrontEndTemplate diff = 0.
- [ ] Blade output đạt visual fidelity.
- [ ] Core content vẫn SSR.
- [ ] Không có broken asset/external demo link.
- [ ] Design tokens rõ và dùng chung với block.

KIỂM TRA TỐI THIỂU:
- `git diff -- FrontEndTemplate`
- `cd BackEnd && npm run build`
- `cd BackEnd && php artisan test --filter=PublicFrontend`
- `visual regression command nếu đã có`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P19.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 19 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P20.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P20.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Source FrontEndTemplate diff = 0.
- [ ] Blade output đạt visual fidelity.
- [ ] Core content vẫn SSR.
- [ ] Không có broken asset/external demo link.
- [ ] Design tokens rõ và dùng chung với block.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 20 — THEME STUDIO CHO WEBSITE PUBLIC

**Phase:** 03 — Media & Frontend  
**Flag:** `REQUIRED`

## Mục tiêu

Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin.

## Điều kiện tiên quyết

1. P18 DONE; P19 có thể DONE hoặc DEFERRED.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P20 — Theme Studio cho website public
PHẠM VI: 03 — Media & Frontend
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P20.
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
Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin.

NHIỆM VỤ BẮT BUỘC:
1. Tạo `hongvan_themes`, `hongvan_theme_versions`, active/published version và schema token allowlist.
2. Token gồm colors semantic, fonts allowlist, sizes, spacing scale, radii, shadows, container widths, buttons, headings, section gaps và animation presets.
3. Không cho arbitrary CSS/JS mặc định.
4. Tạo API draft/preview/publish/rollback theme với permission.
5. Tạo Angular Theme Studio theo style admin template; property controls typed.
6. Tạo CSS variable compiler server-side hoặc build runtime an toàn; cache output theo version.
7. Preview theme qua signed preview và Page Builder renderer.
8. Nếu FrontEndTemplate đã port, khởi tạo token từ template; nếu chưa, dùng neutral base và ghi cần remap sau P19.
9. Audit publish/rollback.
10. Test invalid token, versioning và cache invalidation.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Theme public tách khỏi user admin theme.
- [ ] Published pages dùng published theme version.
- [ ] Rollback không mất lịch sử.
- [ ] Không inject CSS/JS tùy ý.
- [ ] Preview và public dùng cùng token compiler.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Theme`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P20.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 20 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P21.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P21.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Theme public tách khỏi user admin theme.
- [ ] Published pages dùng published theme version.
- [ ] Rollback không mất lịch sử.
- [ ] Không inject CSS/JS tùy ý.
- [ ] Preview và public dùng cùng token compiler.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 21 — PAGE BUILDER SCHEMA VÀ BLOCK REGISTRY

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Xây lõi document, block registry, validation, migrations và API metadata phía server.

## Điều kiện tiên quyết

1. P18, P20 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P21 — Page Builder schema và block registry
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P21.
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
Xây lõi document, block registry, validation, migrations và API metadata phía server.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/models pages, translations, versions, schedules, locks, templates, preview sessions theo blueprint.
2. Định nghĩa PageDocument schema version 1 với block id/type/version/props/style/visibility/bindings/children.
3. Tạo server BlockRegistry; mỗi block khai báo type, version, schema, defaults, parent/children, renderer, sanitizer, data dependencies.
4. Tạo validator có path-specific errors, limit payload/depth/block count và cycle detection.
5. Tạo block version migrator; import document cũ phải migrate tuần tự.
6. Tạo API registry metadata cho Angular, không lộ internal class/path.
7. Tạo Page CRUD metadata/draft shell, chưa cần UI builder đầy đủ.
8. Tạo cache key/tag contract.
9. Tạo tests invalid type, arbitrary view, script payload, too deep, duplicate id, invalid child.
10. Cập nhật PAGE_BUILDER_CONTRACT theo code thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Database không lưu Blade/PHP.
- [ ] Unknown block bị reject.
- [ ] Published version model immutable contract.
- [ ] Registry API typed.
- [ ] Security tests pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PageBuilder`
- `cd BackEnd && php artisan test --filter=PageDocument`
- `cd BackEnd && vendor/bin/phpstan analyse app/Domain/PageBuilder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P21.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 21 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P22.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P22.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Database không lưu Blade/PHP.
- [ ] Unknown block bị reject.
- [ ] Published version model immutable contract.
- [ ] Registry API typed.
- [ ] Security tests pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 22 — BLOCK LAYOUT NỀN

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens.

## Điều kiện tiên quyết

1. P21 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P22 — Block layout nền
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P22.
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
Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens.

NHIỆM VỤ BẮT BUỘC:
1. Triển khai Section, Container, Stack, Grid, Columns, Spacer, Divider; chỉ thêm Tabs/Accordion nếu template contract đã sẵn sàng.
2. Mỗi block có registry schema, defaults, responsive style allowlist, Blade view, sample fixture và tests.
3. Định nghĩa allowed parent/children và max nesting.
4. Columns/Grid responsive dùng preset/layout tokens, không cho raw CSS grid.
5. Section background hỗ trợ color token/media/gradient allowlist nếu theme cho phép.
6. Spacing chỉ dùng scale token hoặc bounded custom values.
7. Renderer semantic và không tạo div thừa quá mức.
8. Tạo block catalog docs với props và examples.
9. Test nested invalid, mobile stack và escaping.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Layout blocks render trong public Blade.
- [ ] Không arbitrary CSS.
- [ ] Nested constraints rõ.
- [ ] Mobile behavior test.
- [ ] Preview fixture tạo được.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=LayoutBlock`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P22.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 22 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P23.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P23.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Layout blocks render trong public Blade.
- [ ] Không arbitrary CSS.
- [ ] Nested constraints rõ.
- [ ] Mobile behavior test.
- [ ] Preview fixture tạo được.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 23 — BLOCK NỘI DUNG VÀ MEDIA

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai block nội dung phổ biến và media, với sanitization và accessibility.

## Điều kiện tiên quyết

1. P22 DONE.
2. P16 Media DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P23 — Block nội dung và media
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P23.
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
Triển khai block nội dung phổ biến và media, với sanitization và accessibility.

NHIỆM VỤ BẮT BUỘC:
1. Triển khai Heading, RichText, Button, Icon, List, Quote, Table, Badge/Card, Image, ImageText, Gallery, VideoEmbed, LogoCloud, FAQ.
2. Heading có level allowlist và rule tránh nhiều H1 mặc định.
3. RichText dùng editor output có schema/sanitizer server; chặn scripts/events/unsafe URLs.
4. Image chọn từ Media, bắt buộc alt hoặc decorative flag; responsive variants, width/height.
5. Video provider allowlist và privacy-friendly embed.
6. Link protocol/target/rel an toàn.
7. Gallery lazy loading và keyboard/accessibility.
8. FAQ có thể tạo structured data sau khi xác minh content.
9. Mỗi block có tests và fixture.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] XSS payload bị loại/reject.
- [ ] Media usage được ghi khi document save/publish.
- [ ] Alt/decorative validation.
- [ ] Markup accessible.
- [ ] No N+1 media queries.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=ContentBlock`
- `cd BackEnd && php artisan test --filter=MediaBlock`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P23.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 23 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P24.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P24.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] XSS payload bị loại/reject.
- [ ] Media usage được ghi khi document save/publish.
- [ ] Alt/decorative validation.
- [ ] Markup accessible.
- [ ] No N+1 media queries.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 24 — BLOCK DỮ LIỆU NGHIỆP VỤ

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý.

## Điều kiện tiên quyết

1. P21–P23 DONE. Domain chưa tồn tại có thể dùng data-source contracts/fakes, sau module tương ứng hoàn thiện adapter.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P24 — Block dữ liệu nghiệp vụ
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P24.
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
Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa DataSourceRegistry server: products, product categories, crop solutions, services, vehicles/fleet, routes, warehouses, stats, partners, certifications, projects, posts.
2. Mỗi binding chỉ cho filter/sort/limit/preset allowlist; không nhận raw SQL/column.
3. Triển khai block types: hero, product grid, category grid, crop grid, service grid, fleet, route list, warehouse cards, stats, partner logos, certificate list, project list, post list, CTA, breadcrumb.
4. Với domain chưa có, renderer trả empty state có kiểm soát trong preview và không crash public.
5. Tách query/data loading khỏi Blade.
6. Thêm cache dependency tags để entity update invalidates page fragment.
7. Thiết kế preview sample data option chỉ trong preview, không rò ra production.
8. Test injection, limit max, empty state, unpublished entity exclusion và locale.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không query tùy ý từ page document.
- [ ] Chỉ entity published xuất hiện public.
- [ ] Empty data không phá layout.
- [ ] Cache invalidation contract rõ.
- [ ] Dynamic block query không N+1.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=DynamicBlock`
- `cd BackEnd && vendor/bin/phpstan analyse app/Domain/PageBuilder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P24.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 24 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P25.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P25.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không query tùy ý từ page document.
- [ ] Chỉ entity published xuất hiện public.
- [ ] Empty data không phá layout.
- [ ] Cache invalidation contract rõ.
- [ ] Dynamic block query không N+1.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 25 — BLOCK FORM VÀ CTA TẠO LEAD

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility.

## Điều kiện tiên quyết

1. P21–P23 DONE. Lead domain có thể dùng contract, hoàn thiện persistence ở P38.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P25 — Block form và CTA tạo lead
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P25.
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
Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa form block types cố định: contact, product quote, transport request, warehouse request.
2. Không cho Page Builder tạo arbitrary backend field/action; dùng field registry và form definition version.
3. Field schema: label, help, required, validation preset, consent, layout; không cho executable code.
4. Render Blade với CSRF, honeypot, accessible errors, success state.
5. Tạo public endpoint contracts, idempotency/dedup key và rate limit; persistence adapter có thể placeholder test double trước P38.
6. Product quote block tự bind product context khi trên product page.
7. Transport/warehouse fields theo charter.
8. Không gửi email trực tiếp trong request; queue notification.
9. Test spam, validation, duplicate, consent và tampering hidden context.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Arbitrary field/action bị reject.
- [ ] Form keyboard/screen-reader usable.
- [ ] Rate limit/honeypot hoạt động.
- [ ] Context product không thể bị giả mạo mà không validate.
- [ ] No synchronous slow email.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=FormBlock`
- `cd BackEnd && php artisan test --filter=PublicForm`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P25.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 25 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P26.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P26.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Arbitrary field/action bị reject.
- [ ] Form keyboard/screen-reader usable.
- [ ] Rate limit/honeypot hoạt động.
- [ ] Context product không thể bị giả mạo mà không validate.
- [ ] No synchronous slow email.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 26 — EDITOR KÉO THẢ PAGE BUILDER TRONG ANGULAR

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo.

## Điều kiện tiên quyết

1. P21 registry API DONE.
2. P06 admin template DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P26 — Editor kéo thả Page Builder trong Angular
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P26.
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
Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo.

NHIỆM VỤ BẮT BUỘC:
1. Tạo lazy feature page-builder theo cấu trúc template admin.
2. Load block registry metadata từ server và cache theo version.
3. Thiết kế typed PageDocument models, immutable operations add/move/reorder/duplicate/delete/update.
4. Dùng Angular CDK drag-drop hoặc primitive tương thích template; enforce allowed parent/children cả client và server.
5. UI gồm palette/search/category, tree/layers, canvas iframe host placeholder, properties panel, breadcrumbs selection, toolbar.
6. Tạo dynamic property editor theo schema control allowlist; không eval schema/code.
7. Undo/redo có bounded history; dirty state; keyboard shortcuts; confirm navigation.
8. Responsive device modes desktop/tablet/mobile và visibility/style overrides.
9. Permission view/edit/publish tách rõ.
10. Autosave orchestration contract nhưng chưa live preview hoàn chỉnh trước P27.
11. Unit tests cho document operations, nested DnD, duplicate id prevention và undo/redo.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Editor build pass.
- [ ] Không dùng `any` cho document core.
- [ ] Invalid nesting bị chặn UI và vẫn được server reject.
- [ ] Undo/redo ổn định.
- [ ] UI đúng admin template.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P26.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 26 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P27.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P27.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Editor build pass.
- [ ] Không dùng `any` cho document core.
- [ ] Invalid nesting bị chặn UI và vẫn được server reject.
- [ ] Undo/redo ổn định.
- [ ] UI đúng admin template.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 28 — VERSIONING VÀ XUẤT BẢN PAGE

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation.

## Điều kiện tiên quyết

1. P27 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P28 — Versioning và xuất bản page
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P28.
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
Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation.

NHIỆM VỤ BẮT BUỘC:
1. Định nghĩa draft working state và milestone versions; tránh tạo version mỗi keystroke.
2. Save tạo immutable page version với author, note, checksum, schema version.
3. Publish now cập nhật published version trong transaction, audit, invalidate cache, sitemap signal.
4. Schedule publish có timezone input nhưng lưu UTC, xử lý bằng scheduler/queue idempotent.
5. Rollback tạo version mới từ version cũ và publish theo explicit action.
6. Angular UI history compare metadata, preview version, publish dialog, schedule, rollback confirmation.
7. Optimistic concurrency bằng version/checksum; conflict trả 409 và UI xử lý reload/compare.
8. Không cho publish document invalid, missing required SEO/title/translation theo policy.
9. Test race, duplicate scheduler run, rollback và permission.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Published version không bị update.
- [ ] Concurrent edit không silent overwrite.
- [ ] Schedule đúng timezone.
- [ ] Cache invalidated.
- [ ] Audit đầy đủ.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PagePublishing`
- `cd BackEnd && php artisan schedule:test hoặc test scheduler phù hợp`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P28.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 28 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P29.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P29.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Published version không bị update.
- [ ] Concurrent edit không silent overwrite.
- [ ] Schedule đúng timezone.
- [ ] Cache invalidated.
- [ ] Audit đầy đủ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 29 — PAGE TEMPLATES, IMPORT/EXPORT VÀ EDIT LOCKS

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật.

## Điều kiện tiên quyết

1. P28 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P29 — Page templates, import/export và edit locks
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P29.
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
Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật.

NHIỆM VỤ BẮT BUỘC:
1. Tạo page templates/version, categories và permissions.
2. Cho save page as template, create page from template, duplicate page với reset slug/status.
3. Import/export JSON kèm manifest schema/theme/block versions; không chứa secret/private media URLs.
4. Import qua schema migration, block registry validation, media reference mapping và size limits.
5. Tạo edit lock/heartbeat với TTL, owner, force unlock permission và audit.
6. UI template library, export/download, import validation report, lock banner.
7. Không dùng lock vĩnh viễn; recover sau crash.
8. Test malicious import, old schema migration, missing block/media, concurrent locks.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Import không thực thi code.
- [ ] Template/version immutable hợp lý.
- [ ] Lock không làm mất nội dung.
- [ ] Force unlock restricted.
- [ ] Export có thể round-trip.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=PageTemplate`
- `cd BackEnd && php artisan test --filter=PageImport`
- `cd Admin && npm test -- --watch=false`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P29.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 29 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P30.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P30.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Import không thực thi code.
- [ ] Template/version immutable hợp lý.
- [ ] Lock không làm mất nội dung.
- [ ] Force unlock restricted.
- [ ] Export có thể round-trip.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 30 — MENU, HEADER, FOOTER VÀ GLOBAL REGIONS

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning.

## Điều kiện tiên quyết

1. P28 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P30 — Menu, header, footer và global regions
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P30.
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
Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning.

NHIỆM VỤ BẮT BUỘC:
1. Tạo menus/menu items nested với locale, type internal/external/entity/anchor, order, target/rel, permission/publish status.
2. Validate cycle/depth, URL protocol và broken entity reference.
3. Tạo global regions: header, footer, top bar, floating contact, footer columns; document/block versioning như page nhưng scope riêng.
4. Cho theme/page chọn region version hoặc dùng site default.
5. Angular menu tree DnD và global region editor dùng Page Builder.
6. Public renderer cache menu/region và invalidate đúng.
7. Active state/breadcrumb semantics/accessibility.
8. Không cho external link javascript protocol.
9. Test nested menu, locale, publish, cache, broken reference.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Menu quản trị kéo thả được.
- [ ] Header/footer public lấy dữ liệu version published.
- [ ] Không hardcode navigation.
- [ ] Accessible mobile navigation.
- [ ] Region preview đúng frontend style.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Menu`
- `cd BackEnd && php artisan test --filter=GlobalRegion`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P30.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 30 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P31.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P31.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Menu quản trị kéo thả được.
- [ ] Header/footer public lấy dữ liệu version published.
- [ ] Không hardcode navigation.
- [ ] Accessible mobile navigation.
- [ ] Region preview đúng frontend style.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 31 — ROUTING PUBLIC VÀ CÁC TRANG LÕI

**Phase:** 04 — Page Builder  
**Flag:** `REQUIRED`

## Mục tiêu

Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản.

## Điều kiện tiên quyết

1. P28–P30 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P31 — Routing public và các trang lõi
PHẠM VI: 04 — Page Builder
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P31.
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
Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản.

NHIỆM VỤ BẮT BUỘC:
1. Tạo route resolver cho home, page slug, locale strategy, canonical trailing slash và reserved paths.
2. Tạo core page records/templates: home, giới thiệu, liên hệ, sản phẩm, dịch vụ, vận chuyển, kho bãi, tin tức, năng lực; nội dung có thể trống/seed demo có nhãn.
3. Unpublished/draft không public; preview chỉ signed.
4. Slug đổi tạo redirect 301 theo policy; deleted có 404/410.
5. Tạo branded 404/500/maintenance bằng Blade, không rò lỗi.
6. Breadcrumb từ page/menu/entity.
7. Route cache compatible.
8. Test collision với `/admin`, `/api`, `/preview`, assets và entity routes.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Page publish truy cập được SSR.
- [ ] Draft không lộ.
- [ ] Reserved path không bị page chiếm.
- [ ] Canonical/redirect đúng.
- [ ] Error pages an toàn.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan route:list`
- `cd BackEnd && php artisan route:cache`
- `cd BackEnd && php artisan test --filter=PublicPageRouting`
- `cd BackEnd && php artisan route:clear`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P31.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 31 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P32.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P32.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Page publish truy cập được SSR.
- [ ] Draft không lộ.
- [ ] Reserved path không bị page chiếm.
- [ ] Canonical/redirect đúng.
- [ ] Error pages an toàn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 32 — DOMAIN SẢN PHẨM PHÂN BÓN

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây schema/domain sản phẩm, danh mục, thương hiệu, thuộc tính, media và pricing modes đúng website giới thiệu.

## Điều kiện tiên quyết

1. P14, P16 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P32 — Domain sản phẩm phân bón
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P32.
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
Xây schema/domain sản phẩm, danh mục, thương hiệu, thuộc tính, media và pricing modes đúng website giới thiệu.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations categories/translations, brands/translations, products/translations, media, tags, attributes/specifications, related products.
2. Product fields: public_id, sku/code, status/draft/published, category/brand, origin, packaging, featured, publish dates, SEO relation.
3. Pricing fields đúng blueprint; validator theo price_mode.
4. Price resolver/ViewModel: fixed/from/range/contact/market/dealer/quantity; null/zero không hiện `0đ`.
5. Không tạo inventory, cart, order, checkout, payment tables.
6. Thiết lập slug locale, soft delete, audit stamps, indexes và unique constraints.
7. Tạo factories/seed minimal và policies.
8. Tạo unit tests mọi price mode, zero/null, currency/unit/note.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Tất cả bảng prefix.
- [ ] Price resolver đúng yêu cầu.
- [ ] Không có e-commerce schema.
- [ ] Translations và slugs hoạt động.
- [ ] Migration fresh/rollback pass.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --seed --env=testing`
- `cd BackEnd && php artisan test --filter=ProductPrice`
- `cd BackEnd && php ../scripts/check-table-prefix.php`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P32.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 32 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P33.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P33.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Tất cả bảng prefix.
- [ ] Price resolver đúng yêu cầu.
- [ ] Không có e-commerce schema.
- [ ] Translations và slugs hoạt động.
- [ ] Migration fresh/rollback pass.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 33 — QUẢN TRỊ SẢN PHẨM VÀ CATALOG PUBLIC

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm.

## Điều kiện tiên quyết

1. P32 DONE.
2. P16 Media, P31 public routing DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P33 — Quản trị sản phẩm và catalog public
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P33.
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
Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm.

NHIỆM VỤ BẮT BUỘC:
1. Tạo admin API CRUD categories/brands/products/tags/attributes với translation, media ordering, status, bulk publish/archive.
2. Tạo Angular feature product theo template: list, filter, form tabs, media picker, pricing mode conditional fields, preview/publish.
3. Tạo public product category/list/detail Blade, pagination, filter crop/use/category/brand nếu dữ liệu có.
4. Hiển thị giá qua PriceViewModel; contact CTA khi không có giá.
5. Không hiển thị stock/cart/buy now.
6. Gắn product quote form với product context.
7. Tạo related products và structured content hooks.
8. Tạo Page Builder data source adapter product/category.
9. SEO/JSON-LD ở mức placeholder tích hợp P42/P43.
10. Test permissions, validation, public published visibility, filters và price UI.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Admin CRUD đầy đủ.
- [ ] Public chỉ thấy published.
- [ ] Price/contact đúng mọi mode.
- [ ] CTA gửi đúng product ID an toàn.
- [ ] No N+1.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Product`
- `cd Admin && npm run lint && npm test -- --watch=false && npm run build:laravel`
- `public product smoke/E2E`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P33.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 33 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P34.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P34.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Admin CRUD đầy đủ.
- [ ] Public chỉ thấy published.
- [ ] Price/contact đúng mọi mode.
- [ ] CTA gửi đúng product ID an toàn.
- [ ] No N+1.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 34 — GIẢI PHÁP THEO CÂY TRỒNG

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO.

## Điều kiện tiên quyết

1. P33 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P34 — Giải pháp theo cây trồng
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P34.
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
Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO.

NHIỆM VỤ BẮT BUỘC:
1. Tạo migrations/domain crop categories, crops, stages, solutions, translations, product links.
2. Admin CRUD với ordering, media, stage timeline, recommended products, content sections và publish.
3. Public listing/detail SSR theo cây trồng; mỗi stage có nội dung và sản phẩm phù hợp.
4. Không đưa khuyến cáo tuyệt đối hoặc số liệu chuyên môn giả trong seed.
5. Tạo Page Builder data source/block adapter.
6. Tạo internal links product ↔ crop solution.
7. Policies/audit/cache.
8. Test locale, publish, ordering, deleted product handling và no N+1.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Giải pháp có thể quản trị hoàn toàn.
- [ ] Public SSR và SEO-ready.
- [ ] Không hardcode kiến thức giả.
- [ ] Liên kết sản phẩm ổn khi entity archive.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=CropSolution`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P34.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 34 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P35.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P35.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Giải pháp có thể quản trị hoàn toàn.
- [ ] Public SSR và SEO-ready.
- [ ] Không hardcode kiến thức giả.
- [ ] Liên kết sản phẩm ổn khi entity archive.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 35 — MODULE DỊCH VỤ CHUNG

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Quản trị dịch vụ công ty ngoài các entity vận chuyển/kho chuyên biệt và render public.

## Điều kiện tiên quyết

1. P31 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P35 — Module dịch vụ chung
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P35.
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
Quản trị dịch vụ công ty ngoài các entity vận chuyển/kho chuyên biệt và render public.

NHIỆM VỤ BẮT BUỘC:
1. Tạo service categories/services/translations/media với status/order/featured/CTA.
2. Admin CRUD và Page Builder data source.
3. Public service listing/detail Blade.
4. Cho service liên kết form contact/quote phù hợp.
5. Vận chuyển/kho có thể là category hoặc link đến module chuyên biệt nhưng không duplicate content.
6. SEO relation/cache/audit/policies.
7. Test publish/locale/CTA.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Service module không chồng dữ liệu transport/warehouse.
- [ ] Admin/public hoạt động.
- [ ] Dynamic block adapter hoàn chỉnh.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Service`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P35.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 35 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P36.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P36.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Service module không chồng dữ liệu transport/warehouse.
- [ ] Admin/public hoạt động.
- [ ] Dynamic block adapter hoàn chỉnh.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 36 — VẬN CHUYỂN, ĐỘI XE VÀ TUYẾN

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây module giới thiệu năng lực vận chuyển và nhận yêu cầu, không biến thành TMS điều phối.

## Điều kiện tiên quyết

1. P31, P35 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P36 — Vận chuyển, đội xe và tuyến
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P36.
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
Xây module giới thiệu năng lực vận chuyển và nhận yêu cầu, không biến thành TMS điều phối.

NHIỆM VỤ BẮT BUỘC:
1. Tạo vehicle types, vehicles, vehicle media, transport routes, service areas và translations/status/order.
2. Fields giới thiệu: loại xe, tải trọng, thùng/kích thước mô tả, availability display, gallery; không theo dõi GPS/dispatch.
3. Admin CRUD đội xe, tuyến, khu vực, featured.
4. Public vận chuyển overview, fleet, route/service area pages hoặc sections SSR.
5. Tạo Page Builder data sources/blocks.
6. Form transport request contract fields: pickup, delivery, cargo, weight, vehicle preference, date, contact.
7. Không tính cước tự động nếu chưa có business formula.
8. Policies/audit/cache/tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có TMS scope creep.
- [ ] Public thể hiện năng lực.
- [ ] Transport request context hợp lệ.
- [ ] Dynamic blocks dùng published data.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Transportation`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P36.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 36 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P37.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P37.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có TMS scope creep.
- [ ] Public thể hiện năng lực.
- [ ] Transport request context hợp lệ.
- [ ] Dynamic blocks dùng published data.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 37 — KHO BÃI VÀ YÊU CẦU THUÊ KHO

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS.

## Điều kiện tiên quyết

1. P31, P35 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P37 — Kho bãi và yêu cầu thuê kho
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P37.
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
Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS.

NHIỆM VỤ BẮT BUỘC:
1. Tạo warehouses/translations/media/facilities/services với address/map coordinates optional, area/capacity descriptive fields, security/PCCC descriptions, business hours.
2. Không tạo stock bins, inbound/outbound operations hoặc inventory ledger.
3. Admin CRUD kho, gallery, facilities, map, featured/status.
4. Public warehouse listing/detail SSR với map privacy/performance strategy.
5. Tạo Page Builder data sources/blocks.
6. Form warehouse request: goods, required area/volume, duration, start date, storage requirements, location, contact.
7. Policies/audit/cache/tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có WMS scope creep.
- [ ] Kho public hiển thị từ data thật.
- [ ] Map không hardcode key.
- [ ] Request context an toàn.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Warehouse`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P37.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 37 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P38.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P38.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có WMS scope creep.
- [ ] Kho public hiển thị từ data thật.
- [ ] Map không hardcode key.
- [ ] Request context an toàn.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 38 — LEAD, BÁO GIÁ VÀ QUY TRÌNH TIẾP NHẬN

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái.

## Điều kiện tiên quyết

1. P25 form blocks, P33, P36, P37 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P38 — Lead, báo giá và quy trình tiếp nhận
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P38.
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
Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái.

NHIỆM VỤ BẮT BUỘC:
1. Tạo lead core, assignments, status histories, notes, contact/quote items và mapping transport/warehouse request.
2. Status allowlist: new, contacted, qualified/processing, done, spam, archived; transition policy rõ.
3. Public endpoints validate, anti-spam, rate limit, idempotency/dedup và consent.
4. Queue notifications email/database cho team theo settings; failure retry.
5. Admin inbox hợp nhất và views theo loại, filter, assignment, notes nội bộ, status timeline, export permission.
6. Nội dung gốc khách gửi immutable; nhân viên chỉ thêm note/status/assignment.
7. Redact dữ liệu nhạy cảm trong audit; retention/export/delete policy.
8. Dashboard metrics hooks.
9. Test duplicate, permission, transition, notification, immutable original.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Form blocks lưu lead thật.
- [ ] Không sửa nội dung gốc.
- [ ] Assignment/status history đầy đủ.
- [ ] Spam/rate limiting hoạt động.
- [ ] Notification queue không chặn request.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Lead`
- `cd BackEnd && php artisan test --filter=PublicSubmission`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P38.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 38 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P39.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P39.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Form blocks lưu lead thật.
- [ ] Không sửa nội dung gốc.
- [ ] Assignment/status history đầy đủ.
- [ ] Spam/rate limiting hoạt động.
- [ ] Notification queue không chặn request.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 39 — TIN TỨC VÀ KIẾN THỨC

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây CMS bài viết, chuyên mục, tag, author, schedule và public blog SEO-ready.

## Điều kiện tiên quyết

1. P14, P16, P31 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P39 — Tin tức và kiến thức
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P39.
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
Xây CMS bài viết, chuyên mục, tag, author, schedule và public blog SEO-ready.

NHIỆM VỤ BẮT BUỘC:
1. Tạo post categories/translations, posts/translations, tags/pivots, featured image, author, status, publish schedule.
2. Admin editor dùng rich text sanitizer/media picker, preview, schedule, category/tag.
3. Public listing/category/tag/detail SSR, pagination, related posts, author/date.
4. Page Builder post list data source.
5. Không cho raw script/embed ngoài allowlist.
6. RSS optional nếu nằm trong charter; nếu làm phải test.
7. Audit/cache/permissions.
8. Test scheduled publish, locale, XSS, slug redirect, no N+1.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Draft không public.
- [ ] Scheduled post xuất bản idempotent.
- [ ] Rich text an toàn.
- [ ] Public SSR.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Post`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P39.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 39 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P40.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P40.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Draft không public.
- [ ] Scheduled post xuất bản idempotent.
- [ ] Rich text an toàn.
- [ ] Public SSR.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 40 — GALLERY, ĐỐI TÁC, CHỨNG NHẬN VÀ DỰ ÁN

**Phase:** 05 — Business Modules  
**Flag:** `REQUIRED`

## Mục tiêu

Xây các module thể hiện năng lực doanh nghiệp và tái sử dụng media.

## Điều kiện tiên quyết

1. P16, P31 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P40 — Gallery, đối tác, chứng nhận và dự án
PHẠM VI: 05 — Business Modules
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P40.
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
Xây các module thể hiện năng lực doanh nghiệp và tái sử dụng media.

NHIỆM VỤ BẮT BUỘC:
1. Tạo galleries/items, partners, certifications, projects/case studies và translation/media relations.
2. Admin CRUD, ordering, featured/status, document file/download policy cho certification.
3. Public sections/detail khi phù hợp; logo alt, image captions.
4. Page Builder data sources/blocks.
5. Không seed logo/chứng nhận/khách hàng giả.
6. Media usage và delete protection.
7. Cache/audit/permission/tests.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Không có dữ liệu doanh nghiệp giả.
- [ ] Media relations đúng.
- [ ] Public chỉ published.
- [ ] Blocks/data sources hoàn chỉnh.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Showcase`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P40.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 40 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P41.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P41.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Không có dữ liệu doanh nghiệp giả.
- [ ] Media relations đúng.
- [ ] Public chỉ published.
- [ ] Blocks/data sources hoàn chỉnh.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 42 — SEO METADATA VÀ SOCIAL SHARING

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Quản trị SEO ở page/entity level, canonical, robots, OG và defaults có validation.

## Điều kiện tiên quyết

1. P31–P41 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P42 — SEO metadata và social sharing
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P42.
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
Quản trị SEO ở page/entity level, canonical, robots, OG và defaults có validation.

NHIỆM VỤ BẮT BUỘC:
1. Tạo/hoàn thiện polymorphic `hongvan_seo_meta` hoặc relation typed; fields title, description, canonical override, robots, OG, Twitter, focus hints, image.
2. Tạo SEO resolver merge entity → page → global defaults.
3. Admin SEO panel dùng chung với character guidance, preview SERP/social không cam kết pixel exact.
4. Canonical generated từ route, override chỉ URL hợp lệ/permission.
5. Robots defaults: draft/preview/admin noindex; public published index theo setting.
6. OG image dùng Media variants.
7. Title/description render một lần, escaped.
8. Test duplicate tags, fallback, locale/hreflang hooks, malicious canonical.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Mỗi public response có metadata nhất quán.
- [ ] Preview/admin noindex.
- [ ] Không duplicate title/canonical.
- [ ] SEO fields không nằm lẫn trong arbitrary Page Builder JSON.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=SeoMeta`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P42.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 42 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P43.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P43.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Mỗi public response có metadata nhất quán.
- [ ] Preview/admin noindex.
- [ ] Không duplicate title/canonical.
- [ ] SEO fields không nằm lẫn trong arbitrary Page Builder JSON.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 43 — SITEMAP, STRUCTURED DATA, BREADCRUMB VÀ REDIRECT

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả.

## Điều kiện tiên quyết

1. P42 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P43 — Sitemap, structured data, breadcrumb và redirect
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P43.
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
Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả.

NHIỆM VỤ BẮT BUỘC:
1. Tạo sitemap index và sitemap theo entity/locale, chỉ published/canonical, cache và regenerate/invalidate hợp lý.
2. Tạo robots.txt quản trị.
3. Tạo redirect manager `hongvan_redirects`: exact path, locale, status 301/302/410, loop/collision detection.
4. Structured data builders: Organization/LocalBusiness từ settings thật; WebSite; BreadcrumbList; Product; Article; Service; FAQ khi hợp lệ.
5. Product Offer chỉ khi giá public xác định; contact/market/dealer không khai price 0.
6. Hreflang/x-default khi locale enabled.
7. Admin UI redirects/sitemap health/schema preview.
8. Test redirect loop, reserved path, schema JSON encoding/XSS và sitemap exclusion.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Sitemap valid và không lộ draft.
- [ ] Schema không có dữ liệu giả.
- [ ] Redirect loop bị chặn.
- [ ] Breadcrumb tương ứng UI.
- [ ] Price 0 không xuất hiện schema.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Sitemap`
- `cd BackEnd && php artisan test --filter=StructuredData`
- `cd BackEnd && php artisan test --filter=Redirect`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P43.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 43 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P44.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P44.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Sitemap valid và không lộ draft.
- [ ] Schema không có dữ liệu giả.
- [ ] Redirect loop bị chặn.
- [ ] Breadcrumb tương ứng UI.
- [ ] Price 0 không xuất hiện schema.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 44 — ANALYTICS VÀ COOKIE CONSENT

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance.

## Điều kiện tiên quyết

1. P13 settings, P42 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P44 — Analytics và cookie consent
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P44.
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
Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế settings provider cho GA/Tag Manager/other approved, disabled by default.
2. Tạo consent categories necessary/analytics/marketing, banner/preferences, locale text và policy link.
3. Chỉ inject analytics script sau consent theo law/policy configuration; necessary cookie không cần consent giả.
4. Tạo `hongvan_consent_records` chỉ khi cần server record; giảm dữ liệu/retention.
5. Page Builder không cho arbitrary tracking scripts.
6. Admin analytics settings masked/validated.
7. Thêm event hooks lead submit/product view nhưng không gửi PII.
8. Test no-consent no-script, revoke, CSP nonce/hash compatibility.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Analytics disabled không tạo request ngoài.
- [ ] Không gửi PII.
- [ ] Consent persistence và revoke hoạt động.
- [ ] Không arbitrary script injection.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Consent`
- `frontend browser/E2E consent tests`
- `cd BackEnd && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P44.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 44 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P45.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P45.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Analytics disabled không tạo request ngoài.
- [ ] Không gửi PII.
- [ ] Consent persistence và revoke hoạt động.
- [ ] Không arbitrary script injection.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 45 — DASHBOARD, BÁO CÁO VÀ THÔNG BÁO ADMIN

**Phase:** 06 — Operations UX  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo dashboard hữu ích cho nội dung và lead, không dựng BI quá mức.

## Điều kiện tiên quyết

1. P38–P44 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P45 — Dashboard, báo cáo và thông báo admin
PHẠM VI: 06 — Operations UX
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P45.
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
Tạo dashboard hữu ích cho nội dung và lead, không dựng BI quá mức.

NHIỆM VỤ BẮT BUỘC:
1. Tạo dashboard cards: products, pages/posts drafts, leads new/by type/status, overdue follow-up, recent activity, top viewed/search terms nếu analytics enabled.
2. Charts dùng aggregate API có date range/permission và timezone.
3. Tạo database notification center, unread/read/all, deep link an toàn.
4. Export report nhỏ sync, lớn queue; CSV injection protection.
5. Không hiển thị dữ liệu ngoài permission/team assignment.
6. Admin responsive dashboard theo template.
7. Cache aggregates có invalidation/TTL.
8. Test permission/data isolation/date ranges/export.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Dashboard có dữ liệu thật.
- [ ] Role chỉ thấy scope cho phép.
- [ ] Không N+1.
- [ ] CSV an toàn.
- [ ] Notification deep link không open redirect.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test --filter=Dashboard`
- `cd BackEnd && php artisan test --filter=Report`
- `cd Admin && npm test -- --watch=false && npm run build`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P45.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 45 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P46.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P46.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Dashboard có dữ liệu thật.
- [ ] Role chỉ thấy scope cho phép.
- [ ] Không N+1.
- [ ] CSV an toàn.
- [ ] Notification deep link không open redirect.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 46 — ACCESSIBILITY, RESPONSIVE VÀ PERFORMANCE

**Phase:** 06 — SEO & Experience  
**Flag:** `REQUIRED`

## Mục tiêu

Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý.

## Điều kiện tiên quyết

1. Core modules/page builder DONE; FrontEndTemplate có thể đã port hoặc neutral theme được chấp nhận.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P46 — Accessibility, responsive và performance
PHẠM VI: 06 — SEO & Experience
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P46.
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
Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý.

NHIỆM VỤ BẮT BUỘC:
1. Audit semantic headings, landmarks, labels, focus, keyboard, contrast, reduced motion, alt/decorative, dialogs, tables, errors.
2. Sửa public header/menu/forms/gallery/page blocks và admin critical workflows.
3. Responsive test breakpoints template: mobile/tablet/desktop; no horizontal overflow.
4. Tối ưu image variants, sizes/srcset, lazy/eager hero, dimensions, font loading, JS plugins.
5. Cache published pages/data, Vite chunking/admin lazy routes, remove unused heavy dependencies.
6. Đo baseline Lighthouse/Web Vitals trên representative pages; ghi môi trường và không làm số liệu giả.
7. Set performance budgets cho public CSS/JS/image và admin chunks.
8. Test axe/Playwright nếu tool compatible.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Critical accessibility violations được xử lý hoặc documented exception.
- [ ] Public core content usable without JS.
- [ ] Performance budgets có CI gate hợp lý.
- [ ] Không hy sinh fidelity vô cớ.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && npm run build`
- `cd Admin && npm run build:laravel`
- `npx playwright test accessibility (nếu configured)`
- `Lighthouse command/report`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P46.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 46 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P47.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P47.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Critical accessibility violations được xử lý hoặc documented exception.
- [ ] Public core content usable without JS.
- [ ] Performance budgets có CI gate hợp lý.
- [ ] Không hy sinh fidelity vô cớ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 47 — SEEDER VÀ DỮ LIỆU MẪU AN TOÀN

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật.

## Điều kiện tiên quyết

1. P32–P46 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P47 — Seeder và dữ liệu mẫu an toàn
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P47.
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
Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật.

NHIỆM VỤ BẮT BUỘC:
1. Tạo idempotent seeders: permissions/roles, super admin từ env, languages, settings defaults, theme, page templates, product categories, demo products, services, crops, warehouses/vehicles demo có nhãn DEMO.
2. Không seed MST, địa chỉ, hotline, chứng nhận, partner logo hoặc claim năng lực như dữ liệu thật.
3. Tạo demo page documents sử dụng mọi block quan trọng.
4. Media fixture dùng local generated placeholder hợp pháp, không hotlink.
5. Tách `DatabaseSeeder` production-safe và `DemoSeeder` explicit.
6. Factory states draft/published/archived/contact price/fixed/range.
7. Test migrate fresh + seed và repeat seed không duplicate.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Production seeder không tạo fake business claims.
- [ ] Demo seeder rõ nhãn.
- [ ] Không duplicate.
- [ ] Page demo validate registry.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan migrate:fresh --seed --env=testing`
- `cd BackEnd && php artisan db:seed --class=DemoSeeder --env=testing`
- `run DemoSeeder lần 2`
- `cd BackEnd && php artisan test --filter=Seeder`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P47.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 47 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P48.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P48.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Production seeder không tạo fake business claims.
- [ ] Demo seeder rõ nhãn.
- [ ] Không duplicate.
- [ ] Page demo validate registry.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 48 — QA BACKEND TOÀN DIỆN

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks.

## Điều kiện tiên quyết

1. P47 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P48 — QA backend toàn diện
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P48.
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
Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks.

NHIỆM VỤ BẮT BUỘC:
1. Chạy full backend suite trên MySQL test.
2. Chạy Pint test, PHPStan/Larastan ở level đã chốt, composer audit.
3. Thêm architecture tests: table prefix, thin controllers threshold, no DB query in views, Page Builder no arbitrary renderer, no public draft.
4. Đo test coverage theo critical domains, không chạy theo % hình thức; bổ sung khoảng trống auth/RBAC/page publish/media/leads/pricing/SEO.
5. Test queue/scheduler idempotency.
6. Test migration fresh/rollback and route/config cache.
7. Phân loại lỗi existing/new; sửa root cause đúng scope.
8. Tạo `docs/reports/P48_BACKEND_QA.md` với lệnh và kết quả thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Full suite pass hoặc báo blocker cụ thể, không ghi DONE giả.
- [ ] Prefix/security critical tests pass.
- [ ] No pending migration.
- [ ] Composer audit được xử lý/ghi risk.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && php artisan test`
- `cd BackEnd && vendor/bin/pint --test`
- `cd BackEnd && vendor/bin/phpstan analyse`
- `cd BackEnd && composer audit`
- `cd BackEnd && php ../scripts/check-table-prefix.php`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P48.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 48 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P49.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P49.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Full suite pass hoặc báo blocker cụ thể, không ghi DONE giả.
- [ ] Prefix/security critical tests pass.
- [ ] No pending migration.
- [ ] Composer audit được xử lý/ghi risk.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 49 — QA ANGULAR, E2E VÀ VISUAL REGRESSION

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages.

## Điều kiện tiên quyết

1. P48 DONE.
2. Admin build integration DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P49 — QA Angular, E2E và visual regression
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P49.
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
Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages.

NHIỆM VỤ BẮT BUỘC:
1. Chạy lint, unit tests và production build.
2. Thiết lập/hoàn thiện Playwright với auth state an toàn, test database isolation và base URL config.
3. E2E: login/logout, RBAC, theme per user, product CRUD, page builder preview/publish/rollback, media picker, lead workflow, SEO edit.
4. Visual snapshots cho admin shell, media manager (nếu P17 done), Page Builder canvas, public home/product/service desktop/tablet/mobile.
5. Không update snapshots mù quáng; review diff.
6. Accessibility smoke trong E2E.
7. Kiểm tra console errors, failed network, source map/asset path.
8. Tạo `docs/reports/P49_FRONTEND_QA.md`.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Lint/unit/build pass.
- [ ] Critical E2E pass.
- [ ] Visual diffs được review.
- [ ] No console error trong workflows.
- [ ] Deferred source parity được ghi, không che.

KIỂM TRA TỐI THIỂU:
- `cd Admin && npm run lint`
- `cd Admin && npm test -- --watch=false`
- `cd Admin && npm run build:laravel`
- `cd Admin && npx playwright test`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P49.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 49 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P50.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P50.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Lint/unit/build pass.
- [ ] Critical E2E pass.
- [ ] Visual diffs được review.
- [ ] No console error trong workflows.
- [ ] Deferred source parity được ghi, không che.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 50 — BUILD REPRODUCIBLE VÀ CI

**Phase:** 07 — QA & Delivery  
**Flag:** `REQUIRED`

## Mục tiêu

Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile.

## Điều kiện tiên quyết

1. P48–P49 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P50 — Build reproducible và CI
PHẠM VI: 07 — QA & Delivery
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P50.
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
Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile.

NHIỆM VỤ BẮT BUỘC:
1. Hoàn thiện scripts PowerShell/shell: verify prerequisites, backend QA, admin QA, build/sync, smoke.
2. Tạo GitHub Actions hoặc CI vendor-neutral docs: backend job PHP 8.5 + MySQL 8.4 + Redis; admin Node compatible; E2E optional service.
3. CI chạy prefix check, migrations, tests, Pint, PHPStan, npm ci/lint/test/build, audits.
4. Cache dependencies đúng key lockfile, không cache secret/build sai.
5. Build artifact admin/public có checksum; không commit output nếu policy.
6. Source template folders ignored; CI không phụ thuộc source license nếu code đã port.
7. Secret scanning/dependency audit.
8. Branch protection recommendations.
9. Test workflow syntax và local scripts.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] CI từ checkout sạch có thể chạy.
- [ ] Không dùng npm install thay npm ci.
- [ ] Không chứa secret.
- [ ] Fail khi prefix/test/build fail.

KIỂM TRA TỐI THIỂU:
- `validate YAML`
- `run local verify scripts`
- `git diff --check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P50.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 50 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P51.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P51.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] CI từ checkout sạch có thể chạy.
- [ ] Không dùng npm install thay npm ci.
- [ ] Không chứa secret.
- [ ] Fail khi prefix/test/build fail.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 52 — BACKUP, MONITORING VÀ VẬN HÀNH

**Phase:** 08 — Operations  
**Flag:** `REQUIRED`

## Mục tiêu

Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố.

## Điều kiện tiên quyết

1. P51 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P52 — Backup, monitoring và vận hành
PHẠM VI: 08 — Operations
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P52.
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
Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố.

NHIỆM VỤ BẮT BUỘC:
1. Thiết kế backup DB + media + env references, encryption, retention, offsite copy và access control.
2. Tạo backup scripts/jobs có lock, checksum và failure notification; không lưu plain secret.
3. Viết và test restore trên môi trường staging/temporary database.
4. Health endpoints tách liveness/readiness, không lộ config; kiểm tra DB/Redis/queue phù hợp.
5. Log structured request ID, queue failures, scheduler, security; rotation/retention.
6. Monitor disk, DB, Redis, queue backlog, HTTP errors, SSL expiry, backup age.
7. Tạo incident runbooks: app down, DB full, queue stuck, bad deploy, media missing, compromised account.
8. Admin health page restricted nếu triển khai.
9. Tạo `docs/reports/P52_RESTORE_TEST.md` bằng kết quả thật.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Backup chưa restore-test không được coi là hoàn tất.
- [ ] Health không lộ secret.
- [ ] Alert ownership/escalation documented.
- [ ] Retention rõ.

KIỂM TRA TỐI THIỂU:
- `run backup in staging`
- `restore to temporary DB/storage`
- `smoke restored app`
- `verify checksum`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P52.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 52 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P53.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P53.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Backup chưa restore-test không được coi là hoàn tất.
- [ ] Health không lộ secret.
- [ ] Alert ownership/escalation documented.
- [ ] Retention rõ.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

# PROMPT 53 — SECURITY REVIEW TOÀN HỆ THỐNG

**Phase:** 08 — Operations  
**Flag:** `REQUIRED`

## Mục tiêu

Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production.

## Điều kiện tiên quyết

1. P48–P52 DONE.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P53 — Security review toàn hệ thống
PHẠM VI: 08 — Operations
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P53.
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
Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production.

NHIỆM VỤ BẮT BUỘC:
1. Lập threat model: public forms, auth, RBAC, media uploads, Page Builder JSON/preview, rich text, redirects, settings secrets, exports, deployment.
2. Review source-to-sink cho XSS, SQL injection, path traversal, SSRF, upload/RCE, IDOR, CSRF, open redirect, privilege escalation, session fixation, postMessage.
3. Chạy dependency audits và static security tooling phù hợp; không coi scanner là bằng chứng duy nhất.
4. Kiểm tra rate limits, CSP, cookies, CORS, trusted hosts/proxies, debug, error leakage.
5. Review Page Builder không arbitrary Blade/PHP/JS/CSS; import limits.
6. Review media MIME/storage/public serving/SVG.
7. Review admin permissions bằng direct API tests.
8. Tạo findings severity/evidence/fix/test; sửa critical/high trong scope.
9. Tạo `docs/reports/P53_SECURITY_REVIEW.md` không chứa exploit secret.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Critical/high được fix hoặc production blocked rõ.
- [ ] Regression tests cho finding.
- [ ] No false claim 'secure tuyệt đối'.
- [ ] Threat model cập nhật.

KIỂM TRA TỐI THIỂU:
- `cd BackEnd && composer audit`
- `cd Admin && npm audit --omit=dev hoặc policy phù hợp`
- `security test suite`
- `manual permission/upload/preview checks`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P53.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 53 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P54.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P54.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Critical/high được fix hoặc production blocked rõ.
- [ ] Regression tests cho finding.
- [ ] No false claim 'secure tuyệt đối'.
- [ ] Threat model cập nhật.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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


---

# PROMPT 55 — CUTOVER PRODUCTION

**Phase:** 09 — Launch  
**Flag:** `REQUIRED`

## Mục tiêu

Triển khai production theo checklist có backup, rollback và verification cụ thể.

## Điều kiện tiên quyết

1. P54 UAT approved.
2. P53 no unresolved critical/high.
3. Domain/TLS/production env sẵn sàng.

## Prompt dán trực tiếp vào Codex

```text
Bạn đang làm việc tại root project website CÔNG TY TNHH DV VT HỒNG VÂN.

PROMPT HIỆN TẠI: P55 — Cutover production
PHẠM VI: 09 — Launch
TRẠNG THÁI: REQUIRED

Trước khi sửa bất kỳ file nào, bắt buộc:
1. Đọc root AGENTS.md và AGENTS.md gần nhất của các thư mục sẽ sửa.
2. Đọc docs/CODEX_STATE.md, docs/TASK_LEDGER.md và các tài liệu được prompt này nhắc tới.
3. Chạy git status; không xóa, reset hoặc ghi đè thay đổi không thuộc prompt.
4. Tìm kiếm có mục tiêu bằng symbol/path/route/table; không quét toàn repository nếu chưa cần.
5. Cập nhật current_prompt trong docs/CODEX_STATE.md thành P55.
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
Triển khai production theo checklist có backup, rollback và verification cụ thể.

NHIỆM VỤ BẮT BUỘC:
1. Chốt release tag/commit, lockfiles, changelog và artifact checksum.
2. Backup production hiện tại nếu có; verify backup.
3. Build từ checkout sạch qua CI.
4. Deploy code/artifact, set env/secrets, migrate `--force`, cache, queue/scheduler, storage.
5. Thực hiện maintenance/minimal downtime plan.
6. Smoke public home/product/service/transport/warehouse/contact, admin login, media, page preview, lead submit, email/notification, sitemap/robots.
7. Kiểm tra HTTPS, headers, debug off, logs, queue, scheduler, metrics.
8. Nếu gate fail, rollback theo runbook, không vá tay không ghi nhận.
9. Tạo `docs/reports/P55_PRODUCTION_CUTOVER.md` với timestamp, version, checks và kết quả.

KHÔNG ĐƯỢC:
- Không thay đổi ngoài phạm vi prompt.

TIÊU CHÍ NGHIỆM THU:
- [ ] Production healthy.
- [ ] No debug/secret leakage.
- [ ] DB schema đúng prefix.
- [ ] Admin/public deep links hoạt động.
- [ ] Rollback đã sẵn sàng.

KIỂM TRA TỐI THIỂU:
- `production smoke commands`
- `php artisan about --only=environment`
- `queue/scheduler health`
- `TLS/security headers check`

QUY TRÌNH KẾT THÚC:
1. Chạy formatter/linter/test/build đúng phạm vi và ghi chính xác lệnh + kết quả.
2. Xem git diff; bảo đảm không sửa source read-only hoặc file ngoài scope.
3. Cập nhật docs/TASK_LEDGER.md cho P55.
4. Cập nhật docs/CODEX_STATE.md:
   - last_completed_prompt = 55 nếu DONE.
   - status = DONE/PARTIAL/BLOCKED/DEFERRED đúng sự thật.
   - latest test/build.
   - blocker còn lại.
   - next_prompt = P56.
5. Nếu quyết định kiến trúc thay đổi, cập nhật ADR/DECISIONS trong cùng prompt.
6. Chỉ commit khi test liên quan pass và working tree không chứa thay đổi người dùng ngoài scope.
7. Báo cáo cuối gồm: Status, Scope completed, Files changed, Database/API/UI changes,
   Commands run, Tests/build results, Risks, Deferred items, Next prompt.
8. Dừng lại. Không thực hiện P56.
```

## Checklist nghiệm thu dành cho chủ dự án

- [ ] Production healthy.
- [ ] No debug/secret leakage.
- [ ] DB schema đúng prefix.
- [ ] Admin/public deep links hoạt động.
- [ ] Rollback đã sẵn sàng.

## Ghi chú

- Với flag `DEFERRED_ALLOWED`, chỉ được đánh dấu deferred khi external source tương ứng thực sự chưa có hoặc không hợp lệ.
- `DEFERRED` không có nghĩa là đã hoàn tất; phải quay lại trước UAT/production.


---

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
