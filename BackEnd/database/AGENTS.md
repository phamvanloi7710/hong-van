# AGENTS.md — DATABASE

- Mọi bảng và pivot bắt đầu `hongvan_`.
- Migration mới phải rollback được.
- Foreign key/index/unique đầy đủ.
- Mọi `Schema::create` phải khai báo table comment mô tả rõ mục đích của bảng.
- Mọi cột mới hoặc thay đổi, kể cả cột framework, pivot, khóa và timestamp, phải có column comment giải thích ý nghĩa.
- Không chấp nhận migration làm mất comment hiện có; test `DatabaseCommentTest` phải pass.
- Không sửa migration đã chạy production; tạo migration mới.
- Không dùng float cho tiền.
- Không seed thông tin pháp lý giả.
- Seeder demo phải idempotent hoặc có chiến lược rõ.
- Test `migrate:fresh --seed` trên DB test.
- Cập nhật `docs/DATABASE_BLUEPRINT.md` nếu đổi schema lớn.
