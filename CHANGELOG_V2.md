# CHANGELOG — V2

Ngày: 02/08/2026

## Thay đổi

- Chuẩn hóa tên thư mục template public thành `FrontEndTemplate/`.
- Đổi state gate thành `frontend_template_gate`.
- Đổi prompt file P19 thành `19_PORT_PUBLIC_FRONTEND_TEMPLATE.md`.
- Đồng bộ toàn bộ 57 prompt, full sequence, AGENTS, `.gitignore`, docs và cây thư mục.
- Đổi placeholder thành `README_PLACE_FRONTEND_TEMPLATE_HERE.md`.
- Bổ sung hướng dẫn triển khai từ đầu đến production.
- Bổ sung danh sách prompt có checkpoint và mẫu chạy prompt.

## Tương thích

Không trộn file của V1 và V2 trong cùng project. Nếu đã bắt đầu bằng V1, cần đổi đường dẫn/gate một cách có kiểm soát và kiểm tra `git diff` trước khi tiếp tục.
