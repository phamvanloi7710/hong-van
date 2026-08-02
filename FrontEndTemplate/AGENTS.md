# AGENTS.md — FRONTEND TEMPLATE SOURCE

Thư mục `FrontEndTemplate/` là nguồn giao diện website public và mặc định **READ ONLY**.

- Không format, nâng dependency, sửa CSS/JS, đổi asset hoặc xóa file trong source tham chiếu.
- Chỉ kiểm kê rồi port có kiểm soát sang `BackEnd/resources/`.
- Không link runtime production trực tiếp về thư mục này.
- Giữ nguyên README, license, fonts, assets và tài liệu cần thiết để đối chiếu.
- Không chạy `npm install`, `npm ci`, `npm run build` hoặc plugin không rõ nguồn nếu prompt không cho phép rõ ràng.
- Khi source có nhiều demo, phải inventory và chọn bằng bằng chứng; không tự xóa các demo còn lại.
- Mọi mapping section phải được ghi vào inventory và Page Builder block catalog.
