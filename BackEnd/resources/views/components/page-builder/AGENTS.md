# AGENTS.md — PAGE BUILDER BLADE BLOCKS

- Mỗi view chỉ nhận props đã validate.
- Không query DB.
- Escape mặc định.
- Chỉ render sanitized rich text qua wrapper rõ.
- Dùng design tokens.
- Có semantic HTML và accessibility.
- Giữ data-block-id tối thiểu phục vụ preview, không lộ internal secret.
