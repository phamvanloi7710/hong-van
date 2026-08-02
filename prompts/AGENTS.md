# AGENTS.md — PROMPTS

- Không tự sửa nội dung prompt lịch sử sau khi prompt đã chạy nếu không có lý do.
- Mỗi prompt là một checkpoint.
- Chạy đúng thứ tự.
- Prompt có `DEFERRED_ALLOWED` chỉ được deferred vì source ngoài chưa có.
- Khi hoàn tất prompt, đánh dấu trong `docs/TASK_LEDGER.md`.
- Không gộp nhiều prompt để “nhanh hơn”; mục tiêu là giảm phạm vi và tiết kiệm token.
