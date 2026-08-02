# Git workflow

## Nhánh

- `main` là nhánh tích hợp ổn định.
- Mỗi prompt nên dùng một nhánh ngắn, ví dụ `codex/p04-bootstrap-backend`, khi công việc được thực hiện qua pull request.
- Không force push và không reset hoặc xóa thay đổi chưa commit của người khác.
- Không đưa `Template/`, `FrontEndTemplate/`, `SourceIntegrations/`, secret, dependency hoặc build output bị ignore vào Git bằng `-f`.

## Commit

Mỗi prompt tạo commit nhỏ sau khi các kiểm tra liên quan đạt yêu cầu. Định dạng khuyến nghị:

```text
<type>(Pxx): <mô tả ngắn>
```

Các `type` thường dùng: `feat`, `fix`, `docs`, `test`, `chore`. Không trộn refactor ngoài phạm vi hoặc nhiều prompt vào cùng commit.

## Trước khi commit

1. Chạy test, formatter, linter hoặc build đúng phạm vi.
2. Chạy `git diff --check`.
3. Kiểm tra `git status --short` và staged diff.
4. Xác nhận không có secret, file môi trường, nguồn tham chiếu hoặc output sinh tự động ngoài chủ đích.
5. Cập nhật `docs/CODEX_STATE.md` và `docs/TASK_LEDGER.md`.

## Pull request

Pull request cần nêu prompt, phạm vi thay đổi, migration/API/UI mới, lệnh kiểm tra và kết quả, rủi ro hoặc phần hoãn. Chỉ merge khi CI và review bắt buộc đã đạt. Không tự push hoặc mở pull request nếu chủ dự án chưa yêu cầu.
