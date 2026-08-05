# T237 — Audit UI, crawl, accessibility và performance


## Metadata

- **Giai đoạn:** N — Rà soát lặp, sinh prompt còn thiếu và Release Gate
- **Bao phủ prompt gốc:** `R90`
- **Phụ thuộc:** `T236`
- **File queue:** `tasks/T237_AUDIT_UI_CRAWL_A11Y_PERFORMANCE.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Audit UI, crawl, accessibility và performance phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/`
- `Admin/`
- `docs/`
- `scripts/`
- `docker/`
- `prompts/hongvan-master-v2/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "TODO|FIXME|HACK|window\.prompt|window\.confirm|console\.log|dd\(|dump\(" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Crawl public/admin vi-en-zh desktop/tablet/mobile.
2. broken links/assets/console.
3. keyboard/contrast/bundle/query/cache budgets.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Audit không tự sửa hàng loạt; mỗi gap độc lập sinh một prompt focused.
- Final GO chỉ khi generated queue rỗng và full gates xanh.
- Mọi BLOCKED/DEFERRED có owner, lý do, rủi ro và điều kiện mở khóa.
- Hoàn thành đúng trọng tâm: Crawl public/admin vi-en-zh desktop/tablet/mobile.
- Hoàn thành đúng trọng tâm: broken links/assets/console.
- Hoàn thành đúng trọng tâm: keyboard/contrast/bundle/query/cache budgets.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test
cd BackEnd; vendor\bin\pint --test
cd BackEnd; vendor\bin\phpstan analyse --memory-limit=1G
cd Admin; npm run lint
cd Admin; npm test -- --run
cd Admin; npm run build:laravel
cd Admin; npx playwright test
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T237` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T237` trong message và chỉ stage file thuộc task.
6. Push `main` lên `origin/main` khi gate pass.
7. Xác nhận local HEAD bằng remote HEAD.
8. Báo:
   - trạng thái;
   - root cause/evidence;
   - file thay đổi;
   - API/DB/UI thay đổi;
   - commands và kết quả;
   - rủi ro/blocker;
   - commit SHA và remote SHA.
9. **Dừng lại. Không chạy task kế tiếp.**
