# T001 — Đối chiếu HEAD hiện tại và trạng thái dự án


## Metadata

- **Giai đoạn:** A — Quản trị, baseline và bộ nhớ dự án
- **Bao phủ prompt gốc:** `P00`
- **Phụ thuộc:** `Không`
- **File queue:** `tasks/T001_RECONCILE_CURRENT_HEAD_AND_STATE.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Đối chiếu HEAD hiện tại và trạng thái dự án phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `AGENTS.md`
- `docs/`
- `prompts/`
- `.gitignore`
- `.gitattributes`
- `.env.example`
- `prompts/hongvan-master-v2/`
- `docs/hongvan-master-v2/`
- `scripts/hongvan-master-v2/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "current_prompt|last_completed_prompt|TODO|FIXME|DEFERRED|BLOCKED" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Xác nhận local HEAD, origin/main và working tree.
2. đối chiếu docs/CODEX_STATE.md, TASK_LEDGER.md với commit/source thật.
3. khởi tạo hoặc cập nhật state của Master Pack mà không ghi đè lịch sử.
4. Chạy validator của pack và xác nhận đủ 240 task, queue/state hợp lệ.
5. Xem toàn bộ file Master Pack mới là phạm vi bootstrap của T001; stage và commit chúng cùng state sau khi validation pass.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Mọi kết luận đều gắn với bằng chứng ở HEAD hiện tại, không tin mù quáng tài liệu cũ.
- Không thay đổi source nghiệp vụ ngoài phạm vi task.
- Không làm yếu AGENTS.md hoặc quy tắc bảo mật hiện có.
- Hoàn thành đúng trọng tâm: Xác nhận local HEAD, origin/main và working tree.
- Hoàn thành đúng trọng tâm: đối chiếu docs/CODEX_STATE.md, TASK_LEDGER.md với commit/source thật.
- Hoàn thành đúng trọng tâm: khởi tạo hoặc cập nhật state của Master Pack mà không ghi đè lịch sử.
- Pack được cài đầy đủ, validator PASS và toàn bộ file pack được đưa vào Git trong commit T001.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
git status --short --branch
git log -12 --oneline
git diff --check
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\hongvan-master-v2\validate-pack.ps1 -PackRoot .
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T001` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T001` trong message. Riêng task này phải stage toàn bộ ba thư mục Master Pack đã cài, vì đó là phạm vi bootstrap hợp lệ.
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
