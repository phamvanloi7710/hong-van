# T229 — Hoàn thiện Docker và production deployment


## Metadata

- **Giai đoạn:** M — Seeders, QA, CI, Deployment, Operations, Security, UAT và Handover
- **Bao phủ prompt gốc:** `P51`
- **Phụ thuộc:** `T228`
- **File queue:** `tasks/T229_IMPLEMENT_DOCKER_PRODUCTION_DEPLOYMENT.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Hoàn thiện Docker và production deployment phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `docker/`
- `docker-compose*.yml`
- `Dockerfile*`
- `.gitlab-ci.yml`
- `scripts/`
- `BackEnd/.env.example`
- `docs/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "nginx|php\-fpm|redis|mysql|queue|scheduler|health|APP_DEBUG" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Multi-stage images.
2. Nginx/PHP-FPM/queue/scheduler/MySQL/Redis.
3. health/secrets/volumes/permissions.
4. staging smoke.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- Production image không chứa secret/dev dependency/source map không cần thiết.
- Queue/scheduler/Redis/MySQL/Nginx có health/readiness và restart policy phù hợp.
- Không báo triển khai production thành công nếu chưa có môi trường thật.
- Hoàn thành đúng trọng tâm: Multi-stage images.
- Hoàn thành đúng trọng tâm: Nginx/PHP-FPM/queue/scheduler/MySQL/Redis.
- Hoàn thành đúng trọng tâm: health/secrets/volumes/permissions.
- Hoàn thành đúng trọng tâm: staging smoke.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
docker compose config
docker compose build
git diff --check
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T229` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T229` trong message và chỉ stage file thuộc task.
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
