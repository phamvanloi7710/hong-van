# T019 — Rà mapping model, relation và public_id


## Metadata

- **Giai đoạn:** B — Nền tảng Laravel, Angular, Database và API
- **Bao phủ prompt gốc:** `P08,P09`
- **Phụ thuộc:** `T018`
- **File queue:** `tasks/T019_AUDIT_MODEL_TABLE_AND_PUBLIC_IDS.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Rà mapping model, relation và public_id phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/database/migrations/`
- `BackEnd/app/Models/`
- `BackEnd/config/database.php`
- `BackEnd/tests/Feature/Architecture/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "Schema::create|Schema::table|protected\ \$table|public_id|comment\(" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Kiểm tra protected table/casts/fillable/relations.
2. entity public dùng ULID/public_id ổn định.
3. API không lộ sequence không cần thiết.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Mọi bảng vật lý có tiền tố hongvan_ và không dùng connection-level prefix.
- Mọi bảng/cột mới hoặc thay đổi có comment rõ ràng.
- Migration có rollback, index, foreign key và unique constraint hợp lệ.
- Hoàn thành đúng trọng tâm: Kiểm tra protected table/casts/fillable/relations.
- Hoàn thành đúng trọng tâm: entity public dùng ULID/public_id ổn định.
- Hoàn thành đúng trọng tâm: API không lộ sequence không cần thiết.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test --filter=TablePrefixTest
cd BackEnd; php artisan test --filter=DatabaseCommentTest
cd BackEnd; php artisan migrate:status
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T019` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T019` trong message và chỉ stage file thuộc task.
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
