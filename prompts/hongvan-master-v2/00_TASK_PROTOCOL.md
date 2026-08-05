# TASK PROTOCOL

Mỗi task dùng bốn trạng thái chính:

- `PENDING`: chưa bắt đầu.
- `IN_PROGRESS`: đang làm, phải tiếp tục trước khi chọn task khác.
- `DONE`: đã có thay đổi implementation và mọi gate của task pass.
- `VERIFIED`: implementation sẵn có đã được kiểm chứng ở HEAD hiện tại.

Trạng thái chặn:

- `BLOCKED`: blocker kỹ thuật nội bộ.
- `BLOCKED_EXTERNAL`: thiếu source, quyền, production/staging, domain, credential hoặc quyết định owner.
- `FAILED`: task thực thi nhưng gate đỏ; phải tiếp tục chính task này ở lượt sau.

Không dùng `DONE` nếu chỉ viết tài liệu nhưng implementation bắt buộc chưa tồn tại.
