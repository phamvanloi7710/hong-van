# T227 — Quét dependency, license, secret và container


## Metadata

- **Giai đoạn:** M — Seeders, QA, CI, Deployment, Operations, Security, UAT và Handover
- **Bao phủ prompt gốc:** `P50,P53`
- **Phụ thuộc:** `T226`
- **File queue:** `tasks/T227_DEPENDENCY_LICENSE_SECURITY_SCANS.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Quét dependency, license, secret và container phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/`
- `Admin/`
- `docker/`
- `.gitlab-ci.yml`
- `docs/SECURITY_BASELINE.md`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "eval\(|shell_exec|exec\(|unserialize|innerHTML|bypassSecurityTrust|localStorage|raw" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Composer/npm audit.
2. license allowlist.
3. gitleaks.
4. SAST/container scan.
5. triage findings with evidence.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Mọi finding có severity, evidence, exploitability và remediation cụ thể.
- Critical/High chưa xử lý làm release gate NO-GO.
- Không thêm bypass bảo mật để làm test chạy.
- Hoàn thành đúng trọng tâm: Composer/npm audit.
- Hoàn thành đúng trọng tâm: license allowlist.
- Hoàn thành đúng trọng tâm: gitleaks.
- Hoàn thành đúng trọng tâm: SAST/container scan.
- Hoàn thành đúng trọng tâm: triage findings with evidence.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; composer audit
cd Admin; npm audit --omit=dev
Chạy secret scan/dependency/container scan đã cấu hình trong project.
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T227` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T227` trong message và chỉ stage file thuộc task.
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
