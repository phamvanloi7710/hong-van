# T108 — Làm cứng optimistic concurrency autosave


## Metadata

- **Giai đoạn:** G — Ổn định P28 và P29
- **Bao phủ prompt gốc:** `P28,P29`
- **Phụ thuộc:** `T107`
- **File queue:** `tasks/T108_HARDEN_AUTOSAVE_CONCURRENCY.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Làm cứng optimistic concurrency autosave phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/app/Domain/PageBuilder/PagePublishingManager.php`
- `BackEnd/app/Domain/PageBuilder/PageManager.php`
- `BackEnd/app/Models/PageVersion.php`
- `BackEnd/app/Models/PagePublishSchedule.php`
- `BackEnd/routes/console.php`
- `Admin/src/app/features/page-builder/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "publish|rollback|schedule|expected_checksum|expected_version_id|afterCommit|409" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Kiểm tra draft version/checksum/lock token đồng thời.
2. 409 metadata an toàn.
3. không silent overwrite.
4. serialize client requests.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Publish/rollback/schedule chạy trong transaction và không silent overwrite.
- Cache/sitemap/audit chỉ phát sau transaction commit thành công.
- Scheduler idempotent, dùng UTC và chống chạy trùng.
- Hoàn thành đúng trọng tâm: Kiểm tra draft version/checksum/lock token đồng thời.
- Hoàn thành đúng trọng tâm: 409 metadata an toàn.
- Hoàn thành đúng trọng tâm: không silent overwrite.
- Hoàn thành đúng trọng tâm: serialize client requests.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test --filter=PagePublishingTest
cd BackEnd; php artisan test --filter=PageBuilder
cd Admin; npm test -- --run page-builder
cd Admin; npm run build:laravel
cd Admin; npx playwright test page-builder-preview
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T108` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T108` trong message và chỉ stage file thuộc task.
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
