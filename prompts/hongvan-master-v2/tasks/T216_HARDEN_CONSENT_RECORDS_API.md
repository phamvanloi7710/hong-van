# T216 — Làm cứng consent records và API


## Metadata

- **Giai đoạn:** L — SEO, Analytics và Dashboard
- **Bao phủ prompt gốc:** `P44`
- **Phụ thuộc:** `T215`
- **File queue:** `tasks/T216_HARDEN_CONSENT_RECORDS_API.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Làm cứng consent records và API phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/app/Domain/Analytics/`
- `BackEnd/app/Http/Controllers/Api/V1/Analytics/`
- `BackEnd/config/`
- `BackEnd/resources/views/`
- `BackEnd/app/Http/Middleware/SecurityHeaders.php`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "ConsentManager|ApprovedAnalyticsProviders|CSP|analytics|consent" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Versioned categories/state/update/delete.
2. identifier/privacy/expiry.
3. rate limit and audit policy.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Không tải analytics trước khi có consent phù hợp.
- Provider/script chỉ từ allowlist trong code và CSP đồng bộ.
- Có cơ chế rút consent/xóa record theo chính sách privacy.
- Hoàn thành đúng trọng tâm: Versioned categories/state/update/delete.
- Hoàn thành đúng trọng tâm: identifier/privacy/expiry.
- Hoàn thành đúng trọng tâm: rate limit and audit policy.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test --filter=Analytics
cd BackEnd; php artisan test --filter=Consent
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T216` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T216` trong message và chỉ stage file thuộc task.
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
