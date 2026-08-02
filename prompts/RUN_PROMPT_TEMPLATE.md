# MẪU CHẠY MỖI PROMPT TRONG CODEX

Thay `XX_TEN_PROMPT.md` bằng file prompt thực tế trong `prompts/`.

```text
Hãy mở và thực hiện đúng toàn bộ nội dung file:

prompts/XX_TEN_PROMPT.md

Yêu cầu bắt buộc:
- Xác nhận prompt trước đã DONE hoặc DEFERRED đúng điều kiện.
- Đọc AGENTS.md tại root và AGENTS.md gần nhất của thư mục sẽ sửa.
- Đọc docs/CODEX_STATE.md và docs/TASK_LEDGER.md.
- Chạy git status trước khi sửa; không xóa/reset/ghi đè thay đổi ngoài phạm vi.
- Chỉ làm đúng prompt hiện tại.
- Không sửa source read-only trong Template, FrontEndTemplate hoặc SourceIntegrations.
- Chạy đúng test/lint/build theo prompt và ghi chính xác kết quả.
- Cập nhật docs/CODEX_STATE.md, docs/TASK_LEDGER.md và report/inventory tương ứng.
- Báo cáo file đã tạo/sửa, migration/API/UI, lệnh đã chạy, pass/fail, rủi ro, deferred item và next prompt.
- Dừng lại. Không thực hiện prompt tiếp theo.
```

## Sau khi Codex kết thúc

```powershell
git status --short --branch
git diff --check
git diff --stat
Get-Content .\docs\CODEX_STATE.md
Get-Content .\docs\TASK_LEDGER.md
```

Chỉ commit khi diff đúng phạm vi và test liên quan pass.
