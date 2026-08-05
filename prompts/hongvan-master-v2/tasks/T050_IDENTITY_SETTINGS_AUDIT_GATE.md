# T050 — Cổng tích hợp Identity, Settings, Localization và Audit


## Metadata

- **Giai đoạn:** C — Authentication, RBAC, Preferences, Settings, Localization và Audit
- **Bao phủ prompt gốc:** `P10-P15`
- **Phụ thuộc:** `T049`
- **File queue:** `tasks/T050_IDENTITY_SETTINGS_AUDIT_GATE.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Cổng tích hợp Identity, Settings, Localization và Audit phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/app/Domain/Audit/`
- `BackEnd/app/Models/AuditLog.php`
- `BackEnd/app/Http/Controllers/Api/V1/Audit/`
- `Admin/src/app/features/audit/`
- `BackEnd/config/logging.php`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "AuditTrail|AuditRedactor|append|request_id|before|after" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Xác minh audit append-only/redaction cho auth/identity/settings.
2. chạy full test các module.
3. runtime Admin vi/en/zh và permission smoke.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Audit log append-only và không mất khi entity nguồn bị xóa.
- Dữ liệu nhạy cảm được redaction/hash theo contract.
- Mọi action quản trị quan trọng ghi actor, subject, request ID và thời gian UTC.
- Hoàn thành đúng trọng tâm: Xác minh audit append-only/redaction cho auth/identity/settings.
- Hoàn thành đúng trọng tâm: chạy full test các module.
- Hoàn thành đúng trọng tâm: runtime Admin vi/en/zh và permission smoke.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test --filter=Audit
cd Admin; npm test -- --run audit
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T050` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T050` trong message và chỉ stage file thuộc task.
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
