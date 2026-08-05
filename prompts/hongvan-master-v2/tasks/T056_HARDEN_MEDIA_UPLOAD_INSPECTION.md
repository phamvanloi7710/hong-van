# T056 — Làm cứng kiểm tra upload


## Metadata

- **Giai đoạn:** D — Admin shell và Media Manager
- **Bao phủ prompt gốc:** `P16,P15`
- **Phụ thuộc:** `T055`
- **File queue:** `tasks/T056_HARDEN_MEDIA_UPLOAD_INSPECTION.md`
- **Chế độ:** Audit → Implement nếu thiếu/sai → Verify → Test → Commit → Push → Stop

## Kết quả cần đạt

Làm cứng kiểm tra upload phải được đối chiếu với source ở HEAD hiện tại. Giữ nguyên phần đã đúng, sửa phần thiếu/sai bằng thay đổi nhỏ nhất, có test và bằng chứng runtime phù hợp.

## Đọc bắt buộc trước khi làm

1. `AGENTS.md` ở root và AGENTS gần nhất của file sẽ sửa.
2. `prompts/hongvan-master-v2/00_SHARED_RULES.md`.
3. `prompts/hongvan-master-v2/state/STATE.json`.
4. `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md`, `docs/DECISIONS.md` khi liên quan.
5. Các đường dẫn trọng tâm:

- `BackEnd/app/Domain/Media/`
- `BackEnd/app/Models/Media*.php`
- `BackEnd/app/Http/Controllers/Api/V1/Media/`
- `BackEnd/config/media.php`
- `Admin/src/app/features/media/`
- `SourceIntegrations/StayHubMedia/`

Nếu path không tồn tại hoặc đã đổi tên, tìm theo symbol rồi dùng file thật. Không tạo file song song chỉ vì đoán path.

## Khám phá có mục tiêu

Ưu tiên:

```powershell
rg -n "MediaUpload|variant|visibility|is_locked|usage|trash|restore|content" BackEnd Admin docs scripts docker 2>$null
```

Sau đó đọc đầy đủ các hàm/class/route/model trực tiếp liên quan. Không đổ toàn repository.

## Công việc cụ thể

1. Detect MIME thực, extension match, decode image, size limit.
2. block SVG/executable/polyglot đáng ngờ.

Ngoài các điểm trên:

- Lập root cause ngắn nếu implementation hiện tại sai.
- Giữ backward compatibility hợp lý với module đã chạy sau task gốc.
- Bổ sung hoặc sửa test để chứng minh contract, không chỉ kiểm tra HTTP 200.
- Cập nhật tài liệu/ADR chỉ khi contract hoặc vận hành thay đổi.

## Điều kiện nghiệm thu

- MIME được phát hiện từ nội dung, path do server sinh, SVG/executable bị chặn.
- File đang được tham chiếu không bị xóa vĩnh viễn.
- Streaming không lộ storage path và luôn có nosniff/authorization phù hợp.
- Hoàn thành đúng trọng tâm: Detect MIME thực, extension match, decode image, size limit.
- Hoàn thành đúng trọng tâm: block SVG/executable/polyglot đáng ngờ.

## Test và lệnh tối thiểu

Chạy lệnh phù hợp với source/môi trường thực tế, tối thiểu:

```text
cd BackEnd; php artisan test --filter=Media
cd Admin; npm test -- --run media
cd Admin; npm run build:laravel
cd Admin; npx playwright test media
```

Nếu một lệnh không thể chạy vì environment, không được tuyên bố pass. Ghi command, lỗi, blocker và trạng thái `BLOCKED`/`FAILED` đúng thực tế.

## State, Git và báo cáo cuối

1. Cập nhật task `T056` trong `state/STATE.json` thành `DONE`, `VERIFIED`, `FAILED` hoặc `BLOCKED*`.
2. Ghi summary ngắn vào `docs/hongvan-master-v2/EXECUTION_LOG.md`.
3. Cập nhật `docs/CODEX_STATE.md`/`docs/TASK_LEDGER.md` theo policy project.
4. Chạy `git status`, `git diff --check`, `git diff --stat`.
5. Commit có `T056` trong message và chỉ stage file thuộc task.
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
