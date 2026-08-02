# ADR-010: Admin preferences và localization theo user

- Status: Accepted
- Date: 2026-08-02

## Bối cảnh

Admin Annular cần lưu theme, locale và favorite menu riêng cho từng tài khoản, đồng thời mọi chức năng mới phải hỗ trợ tiếng Việt, tiếng Anh và tiếng Trung.

## Quyết định

- Server là nguồn chân lý qua `hongvan_user_preferences`; local storage chỉ là cache giảm hiện tượng flash khi tải SPA.
- Preference dùng namespace/key có allowlist, hiện gồm `theme`, `locale` và `favorite_menu_ids`.
- Admin UI dùng translation key typed và mỗi key phải có đủ `vi`, `en`, `zh` trong cùng thay đổi.
- API message hướng người dùng dùng Laravel language catalog tương ứng.
- Favorite menu port cơ chế multi-select của Annular template, lưu thứ tự shortcut theo user; icon tim đứng bên trái và shortcut đã chọn chỉ hiện icon + tooltip.
- Các request lưu preference được tuần tự hóa để thao tác liên tiếp không ghi đè sai thứ tự.

## Hệ quả

- Thiết bị khác nhận cùng preference sau khi đăng nhập.
- Xóa hết favorite là trạng thái hợp lệ.
- P14 vẫn chịu trách nhiệm localization đầy đủ cho nội dung CMS/public sau P13; ADR này thiết lập nền i18n bắt buộc từ P12.
