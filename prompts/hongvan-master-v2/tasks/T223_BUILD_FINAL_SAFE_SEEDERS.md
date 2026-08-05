# T223 — Hoàn thiện seeders an toàn và idempotent


## Metadata

- **Giai đoạn:** M — Seeders, QA, CI, Deployment, Operations, Security, UAT và Handover
- **Bao phủ prompt gốc:** `P47`
- **Phụ thuộc:** `T222`
- **File queue:** `tasks/T223_BUILD_FINAL_SAFE_SEEDERS.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Hoàn thiện seeders an toàn và idempotent phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/tests/`
- `Admin/src/**/*.spec.ts`
- `Admin/e2e/`
- `.gitlab-ci.yml`
- `scripts/`
- `docs/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "skip|todo|only\(|flaky|coverage|budget|console\.error" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Identity/settings/theme/menu/regions/pages/business demo coherent.
2. no fake claims/secrets.
3. local/testing guard.
4. rerun no duplicates.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Không tắt/bỏ qua test chỉ để pipeline xanh.
- Không tuyên bố pass khi lệnh chưa chạy hoặc environment thiếu.
- Test kiểm tra state/pointer/database/audit thực, không chỉ status 200.
- Hoàn thành đúng trọng tâm: Identity/settings/theme/menu/regions/pages/business demo coherent.
- Hoàn thành đúng trọng tâm: no fake claims/secrets.
- Hoàn thành đúng trọng tâm: local/testing guard.
- Hoàn thành đúng trọng tâm: rerun no duplicates.

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

1. Cập nhật task `T223` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T223` trong message và chỉ stage file thuộc task.
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
