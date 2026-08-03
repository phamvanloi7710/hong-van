# ADR-026 — Server-driven Angular Page Builder editor

- Status: Accepted
- Date: 2026-08-03

## Decision

Angular Page Builder chỉ dựng công cụ biên tập và đọc metadata block từ registry của server. Admin không tự định nghĩa block type, renderer, parent constraint hoặc schema tùy ý. Document được thay đổi bằng các thao tác immutable có kiểm tra parent/child, depth, tổng số block, ID duy nhất và cycle trước khi đưa vào history.

Editor dùng history có giới hạn cho undo/redo, theo dõi dirty state, cảnh báo khi rời trang và autosave có debounce. Conflict `409` không ghi đè state hiện tại; người dùng phải tải lại hoặc xử lý trước khi lưu tiếp. Quyền xem, chỉnh sửa và xuất bản được tách lần lượt theo `pages.view`, `pages.update` và `pages.publish`.

Canvas Angular chỉ là iframe host. Markup website public vẫn do Blade renderer của server tạo; P27 sẽ bổ sung preview session và giao thức `postMessage` có kiểm tra origin, token và schema version.

## Consequences

- Thêm block hoặc đổi schema phải thực hiện tại server registry và giữ contract typed tương thích với Admin.
- Mọi thao tác document mới phải trả về document mới, không mutate state đang nằm trong history.
- Angular không được dựng lại Blade public, nhận HTML tùy ý để chèn vào DOM hoặc thực thi CSS/JavaScript từ document.
- Preview, publish và versioning tiếp tục là các bước riêng P27-P29; P26 không giả lập các endpoint chưa tồn tại.
