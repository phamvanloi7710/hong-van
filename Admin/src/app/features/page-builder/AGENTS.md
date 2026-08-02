# AGENTS.md — PAGE BUILDER ADMIN

- Canvas chính là Blade iframe preview.
- Angular không tái tạo markup frontend.
- DnD thao tác trên document model có immutable operations.
- Có undo/redo.
- Mọi block props lấy schema từ server registry.
- Property form typed/dynamic nhưng phải validate server.
- postMessage kiểm tra origin, token và schema.
- Autosave debounce; không ghi DB mỗi keystroke.
- Không cho arbitrary HTML/CSS/JS.
- Test reorder, nested constraints, delete/duplicate, responsive settings, autosave conflict.
