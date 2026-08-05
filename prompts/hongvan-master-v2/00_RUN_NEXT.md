# CHẠY TASK TIẾP THEO

Thực hiện đúng thuật toán này và chỉ chạy **một task**.

## 1. Đọc state và queue

Đọc:

```text
prompts/hongvan-master-v2/00_SHARED_RULES.md
prompts/hongvan-master-v2/00_TASK_PROTOCOL.md
prompts/hongvan-master-v2/00_AUDIT_LOOP_PROTOCOL.md
prompts/hongvan-master-v2/state/STATE.json
prompts/hongvan-master-v2/queue/MASTER_QUEUE.json
prompts/hongvan-master-v2/generated/QUEUE.json
```

## 2. Chọn task

Thứ tự ưu tiên:

1. Nếu `STATE.json.current_task` có trạng thái `IN_PROGRESS` hoặc `FAILED`, tiếp tục đúng task đó.
2. Nếu generated queue có task `PENDING`, `IN_PROGRESS` hoặc `FAILED`, chạy generated task đầu tiên đủ dependency.
3. Nếu generated queue vừa hoàn tất và `audit_recheck_required=true`, chạy lại audit loop `T235 → T239` theo `00_AUDIT_LOOP_PROTOCOL.md`, bất kể vòng trước đã DONE/VERIFIED.
4. Nếu không, chọn task Master đầu tiên trạng thái `PENDING` mà mọi dependency là `DONE` hoặc `VERIFIED`.
5. Không chọn task sau một dependency `BLOCKED`, `BLOCKED_EXTERNAL` hoặc `FAILED`.
6. `T240` chỉ được chọn khi:
   - mọi T001–T239 là DONE/VERIFIED;
   - generated queue rỗng;
   - `audit_recheck_required=false`;
   - `last_zero_gap_audit_at` có giá trị từ vòng audit gần nhất.

Nếu không có task hợp lệ, báo chính xác blocker và dừng.

## 3. Khóa task

Trước khi sửa code:

- Đặt task thành `IN_PROGRESS`.
- Ghi `current_task`, `started_at`, `base_head` trong state.
- Không commit riêng bước khóa nếu chưa có thay đổi task; commit cùng kết quả cuối.

## 4. Thực thi

- Đọc file task tương ứng.
- Tuân thủ toàn bộ `00_SHARED_RULES.md`.
- Audit implementation hiện tại trước khi sửa.
- Nếu đã đúng, chạy test và mark `VERIFIED`.
- Nếu phải sửa, mark `DONE` chỉ sau khi test/build pass.
- Nếu blocked, mark đúng loại và dừng.

## 5. Kết thúc

- Cập nhật state và execution log.
- Test, diff check, commit, push, xác nhận SHA.
- Trả báo cáo theo mẫu của task.
- Dừng, không tự chạy task kế.

## Lệnh người dùng có thể gửi

```text
Đọc prompts/hongvan-master-v2/00_RUN_NEXT.md và thực hiện đúng một task tiếp theo. Sau khi test, commit, push và xác nhận HEAD thì dừng.
```
