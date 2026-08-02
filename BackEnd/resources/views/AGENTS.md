# AGENTS.md — BLADE VIEWS

- Escape output mặc định.
- Chỉ render rich text đã sanitize.
- Page Builder block mapping từ registry, không từ view name trong DB.
- Mỗi block có contract props rõ.
- Một H1 chính theo page template.
- Không query database trực tiếp trong view.
- Preview và public dùng cùng renderer.
- Không chèn script tùy ý từ page data.
