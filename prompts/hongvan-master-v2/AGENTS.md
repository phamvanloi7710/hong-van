# AGENTS.md — HONG VÂN MASTER PROMPT PACK V2

- Áp dụng cho `prompts/hongvan-master-v2/`.
- Mỗi lần chỉ chạy một task.
- Không sửa task đã phát hành trừ khi có task docs/pack riêng.
- `queue/MASTER_QUEUE.json` là danh sách cố định T001–T240.
- `state/STATE.json` là trạng thái thực thi theo HEAD hiện tại.
- Generated prompts chỉ được tạo trong `generated/` bởi T239.
- Không xóa generated task đã hoàn tất; giữ để audit.
- Mọi kết luận task phải có test/evidence và commit SHA.
- Không đánh dấu DONE/VERIFIED bằng suy đoán.
