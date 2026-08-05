# HỒNG VÂN MASTER PACK V2 — QUY TẮC DÙNG CHUNG

Áp dụng cho toàn bộ task trong `prompts/hongvan-master-v2/`.

## 1. Nguồn chân lý và thứ tự đọc

Trước mỗi task:

1. Đọc `AGENTS.md` ở root.
2. Đọc `AGENTS.md` gần nhất trong thư mục sẽ sửa.
3. Đọc `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
4. Đọc `prompts/hongvan-master-v2/state/STATE.json`.
5. Đọc task hiện tại.
6. Đọc `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
7. Tìm theo symbol/route/model/table trước khi mở file lớn.

Không tin rằng một task đã hoàn thành chỉ vì tài liệu cũ hoặc commit message nói vậy. Luôn đối chiếu HEAD, code và test thật.

## 2. Chỉ làm đúng một task

- Mỗi lần chỉ thực hiện một task hoặc một generated task.
- Không tự chạy task kế tiếp.
- Không gộp nhiều task thành một commit lớn.
- Không refactor ngoài phạm vi chỉ vì tiện tay.
- Nếu phát hiện lỗi ngoài phạm vi, ghi vào `docs/hongvan-master-v2/GAP_BACKLOG.md`; không sửa lén.

## 3. Không phá source của chủ dự án

- Không `git reset --hard`.
- Không `git clean -fd`.
- Không force push.
- Không xóa hoặc ghi đè thay đổi chưa commit của người dùng.
- Nếu working tree có thay đổi ngoài task, giữ nguyên và chỉ stage file thuộc task.
- Riêng T001, toàn bộ file mới trong `prompts/hongvan-master-v2/`, `docs/hongvan-master-v2/` và `scripts/hongvan-master-v2/` là phạm vi bootstrap hợp lệ và phải được validate rồi commit cùng T001.
- Khi sửa một hàm hiện hữu, phải đọc đầy đủ toàn bộ hàm và ngữ cảnh liên quan. Không thay bằng pseudo-code, stub hoặc đoạn rút gọn.

## 4. Source chỉ đọc

Mặc định không sửa:

```text
Template/
FrontEndTemplate/
SourceIntegrations/
```

Chỉ inventory, fingerprint và port vào source đích. Không format, nâng package hoặc commit asset có giấy phép từ source tham chiếu nếu task không cho phép rõ ràng.

## 5. Kiến trúc bất biến

- Public website: Laravel Blade SSR.
- Admin: Angular standalone, strict TypeScript, Annular template.
- Admin API: `/api/admin/v1`.
- Public API: `/api/public/v1` khi cần.
- Public Blade không gọi HTTP loopback.
- Controllers mỏng; validation dùng Form Request; business logic ở Action/Service/Domain.
- Authorization dùng route middleware + Policy/Gate/service scope.
- Không tạo repository/base-service chung chung chỉ để bọc Eloquent.
- Queue cho image variants, email, sitemap, report, import/export và tác vụ nặng.

## 6. Database

- Mọi bảng phải bắt đầu `hongvan_`.
- Không dùng connection-level table prefix.
- Mọi bảng và mọi cột phải có comment.
- Migration có rollback, index, foreign key và unique constraint đúng nghiệp vụ.
- Entity public ưu tiên `public_id` ULID; không lộ sequence không cần thiết.
- Lưu thời gian UTC; hiển thị `Asia/Ho_Chi_Minh`.
- Không lưu tiền bằng float/double.
- Không sửa migration đã chạy production nếu policy yêu cầu migration bổ sung.

## 7. Angular

- Standalone components, strict TypeScript, built-in control flow.
- Không dùng `any` nếu không có giải thích và test.
- Component không gọi HTTP trực tiếp; dùng typed data-access service.
- State cục bộ ưu tiên Signals; RxJS dùng cho stream/orchestration.
- Mọi text hiển thị dùng translation key và có đủ `vi`, `en`, `zh`.
- Không dùng `window.alert`, `window.prompt`, `window.confirm` cho workflow chính. Dùng Angular Material dialog typed.
- Mọi task thay đổi Admin phải chạy `npm run build:laravel` và kiểm tra output tại `BackEnd/public/admin/browser`.
- Runtime nghiệm thu trên `http://hongvan.local` khi môi trường local có sẵn.

## 8. Page Builder

- Database chỉ lưu PageDocument JSON versioned đã validate.
- Không lưu hoặc thực thi Blade/PHP/JavaScript/CSS tùy ý từ database.
- Block type/version nằm trong registry code phía server.
- Server là nguồn chân lý của schema, validation, sanitizer, renderer và data source.
- Preview dùng cùng Blade renderer/CSS public qua signed iframe/session.
- Published versions bất biến.
- Autosave không silent overwrite; conflict giữ bản local.
- Rich text sanitize cả client và server, server là quyết định cuối.

## 9. Sản phẩm và phạm vi nghiệp vụ

- Website giới thiệu và tiếp nhận yêu cầu báo giá.
- Không triển khai cart, checkout, payment hoặc order workflow.
- Không hiển thị `0đ`.
- Giá trống/ẩn chuyển thành CTA liên hệ báo giá.
- Structured data không tạo Offer/rating/chứng nhận giả.
- Transportation không mở rộng sang GPS/dispatch/tự tính giá.
- Warehouses không mở rộng sang WMS tồn kho/inbound/outbound.

## 10. Bảo mật

- Sanctum same-origin cookie/session + CSRF.
- Không lưu access token trong localStorage.
- Filter/sort/search dùng allowlist và bind parameters.
- Upload kiểm tra MIME thực, size, extension, decode và path do server sinh.
- Chặn SVG/executable theo policy.
- Không log password/token/cookie/secret/nội dung file/dữ liệu nhạy cảm.
- Preview/import/export/public forms có expiry, ownership, rate limit và audit phù hợp.
- Không tắt middleware/CSRF/CSP/CORS để cho chạy.

## 11. Cách xử lý task đã có implementation

Nếu source hiện tại đã đáp ứng task:

1. Không viết lại.
2. Chạy toàn bộ test tối thiểu của task.
3. Bổ sung test/evidence còn thiếu nếu cần.
4. Mark `VERIFIED` thay vì `DONE` trong state.
5. Commit cập nhật state/evidence với message `docs(Txxx): verify ...`.

Nếu task thiếu hoặc sai:

1. Ghi root cause ngắn trước khi sửa.
2. Sửa nhỏ nhất đủ đúng contract.
3. Chạy test/build.
4. Mark `DONE` khi pass.

Nếu bị chặn bởi môi trường/quyền/source ngoài:

1. Không giả lập thành công.
2. Mark `BLOCKED` hoặc `BLOCKED_EXTERNAL`.
3. Ghi owner, lý do, rủi ro và điều kiện mở khóa.
4. Dừng.

## 12. Test và chất lượng

- Chạy test đúng phạm vi task trước.
- Phase gate chạy full suite theo task.
- Chạy formatter/linter/static analysis liên quan.
- Angular change luôn build production và sync Laravel.
- Migration change phải test fresh/rollback/comment/prefix.
- Không bỏ qua test, nới budget hoặc thêm ignore rộng chỉ để xanh.
- Báo exact command, exit code và kết quả. Không nói “pass” nếu chưa chạy.

## 13. State, báo cáo và Git

Sau task:

1. Cập nhật `prompts/hongvan-master-v2/state/STATE.json`.
2. Cập nhật ngắn `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md` và `docs/TASK_LEDGER.md` khi project policy yêu cầu.
4. Chạy:

```powershell
git status --short --branch
git diff --check
git diff --stat
```

5. Chỉ stage file thuộc task.
6. Commit message có task ID, ví dụ:

```text
fix(T108): harden page autosave concurrency
feat(T137): add public page builder routing
docs(T001): reconcile project state
```

7. Push ngay `main` lên `origin/main` nếu task pass và project policy yêu cầu.
8. Xác nhận:

```powershell
git rev-parse HEAD
git rev-parse origin/main
```

Hai SHA phải trùng trước khi báo hoàn tất.
9. Dừng, không chạy task kế tiếp.

## 14. Tiết kiệm token

- Dùng `rg` theo symbol/route/model/table trước.
- Không đổ toàn bộ repository hoặc file lớn vào chat.
- Không đọc lại file không đổi nếu state/evidence đủ.
- Báo cáo bằng đường dẫn, symbol và diff summary; không in lại toàn file.
- Mỗi task chỉ giữ ngữ cảnh trực tiếp cần thiết.
