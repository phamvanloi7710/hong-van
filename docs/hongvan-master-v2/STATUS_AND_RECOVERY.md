# STATUS VÀ RECOVERY

## Khi Codex bị ngắt giữa task

- Giữ `current_task` và trạng thái `IN_PROGRESS`.
- Lượt sau tiếp tục đúng task, không chọn task mới.
- Kiểm tra working tree và không ghi đè thay đổi dở.

## Khi test đỏ

- Mark task `FAILED`.
- Ghi command, error summary và file liên quan.
- Lượt sau tiếp tục task đó cho đến khi pass hoặc có blocker thật.

## Khi dependency BLOCKED

- Không nhảy qua.
- Ghi owner/điều kiện mở khóa.
- Có thể tạo branch nghiên cứu riêng chỉ khi owner yêu cầu, không merge main giả hoàn tất.

## Khi state hỏng

- Khôi phục từ Git commit gần nhất.
- Đối chiếu EXECUTION_LOG và commit history.
- Không tự mark hàng loạt DONE.
