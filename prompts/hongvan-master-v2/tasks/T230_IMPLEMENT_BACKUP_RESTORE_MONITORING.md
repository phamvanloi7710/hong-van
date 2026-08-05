# T230 — Hoàn thiện backup, restore, monitoring và incident runbooks


## Metadata

- **Giai đoạn:** M — Seeders, QA, CI, Deployment, Operations, Security, UAT và Handover
- **Bao phủ prompt gốc:** `P52`
- **Phụ thuộc:** `T229`
- **File queue:** `tasks/T230_IMPLEMENT_BACKUP_RESTORE_MONITORING.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Hoàn thiện backup, restore, monitoring và incident runbooks phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `scripts/`
- `docker/`
- `docs/`
- `BackEnd/app/Console/Commands/`
- `BackEnd/config/logging.php`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "backup|restore|monitor|alert|incident|retention|schedule" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Encrypted DB/media backups.
2. retention/offsite.
3. restore drill.
4. logs/metrics/alerts.
5. incident/rollback ownership.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Backup bao gồm DB, media và metadata cần thiết; restore có kiểm thử.
- Runbook có owner, trigger, rollback và tiêu chí phục hồi.
- Không ghi secret hoặc dữ liệu production mẫu vào repository.
- Hoàn thành đúng trọng tâm: Encrypted DB/media backups.
- Hoàn thành đúng trọng tâm: retention/offsite.
- Hoàn thành đúng trọng tâm: restore drill.
- Hoàn thành đúng trọng tâm: logs/metrics/alerts.
- Hoàn thành đúng trọng tâm: incident/rollback ownership.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
git diff --check
Kiểm tra dry-run command/script nếu môi trường cho phép.
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T230` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T230` trong message và chỉ stage file thuộc task.
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
