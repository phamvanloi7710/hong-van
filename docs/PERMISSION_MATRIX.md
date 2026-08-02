# PERMISSION MATRIX — KHUNG

Permission format:

```text
<module>.<action>
```

Actions tiêu chuẩn:

```text
view
create
update
delete
restore
publish
export
manage
```

Modules:

```text
dashboard
users
roles
permissions
settings
themes
media
pages
menus
products
crop_solutions
services
transportation
warehouses
leads
posts
showcase
seo
redirects
analytics
audit
system_health
```

Quy tắc:

- Deny by default.
- Publish tách khỏi update.
- Delete/restore tách.
- Export tách.
- Settings/theme/user/role/audit là quyền nhạy cảm.
- Angular chỉ phản ánh quyền; backend Policy/Gate quyết định cuối.
- Matrix thực tế phải được cập nhật khi P11 hoàn tất.
