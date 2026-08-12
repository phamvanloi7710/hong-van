# PERMISSION MATRIX

Permission hệ thống dùng định dạng `<module>.<action>`, deny-by-default và được định nghĩa duy nhất tại `App\Domain\Identity\PermissionRegistry`.

## Registry thực tế

| Module | Actions |
|---|---|
| `dashboard` | `view` |
| `system` | `health` |
| `users` | `view`, `create`, `update`, `delete` |
| `roles` | `view`, `create`, `update`, `delete` |
| `permissions` | `view`, `create`, `update`, `delete` |
| `settings` | `view`, `update`, `manage_settings` |
| `localization` | `view`, `update` |
| `audit` | `view` |
| `products` | `view`, `create`, `update`, `delete`, `restore`, `publish` |
| `crops` | `view`, `create`, `update`, `delete` |
| `crop_solutions` | `view`, `create`, `update`, `delete`, `publish` |
| `services` | `view`, `create`, `update`, `delete`, `restore`, `publish` |
| `transportation` | `view`, `create`, `update`, `delete`, `publish` |
| `warehouses` | `view`, `create`, `update`, `delete`, `publish` |
| `leads` | `view`, `view_all`, `update`, `export` |
| `pages` | `view`, `create`, `update`, `publish`, `export`, `import`, `force_unlock` |
| `posts` | `view`, `create`, `update`, `delete`, `restore`, `publish` |
| `showcase` | `view`, `create`, `update`, `delete`, `restore`, `publish` |
| `seo` | `view`, `update` |
| `themes` | `view`, `update`, `publish` |
| `media` | `view`, `create`, `update`, `delete`, `restore` |

Tổng cộng: **84 permission**. Trong đó 82 permission xuất hiện trực tiếp ở route middleware; `system.health` được kiểm tra qua Gate `system_health`, còn `leads.view_all` được kiểm tra tại `LeadVisibility` để giới hạn phạm vi dữ liệu.

## Nhãn và seed

- Mỗi permission hệ thống có đủ nhãn `vi`, `en`, `zh` trong registry.
- `PermissionSeeder` lưu nhãn tiếng Việt tương thích ngược vào `hongvan_permissions.name` và seed ba bản dịch vào namespace `permissions` của `hongvan_translation_keys`/`hongvan_translation_values`.
- Seeder dùng `updateOrCreate`, giữ permission tùy chỉnh có `is_system=false`, loại permission hệ thống không còn trong registry và đồng bộ Super Admin đúng danh sách hiện hành.
- API trả `name` theo locale hiện tại và `labels` đủ ba ngôn ngữ; Angular chỉ phản ánh quyền, backend vẫn quyết định cuối.

## Quy tắc authorization

- Route admin được bảo vệ bằng Sanctum và permission middleware hoặc Gate tương ứng; endpoint session/preferences chỉ yêu cầu chính user đã xác thực.
- Policy/Gate/service tiếp tục kiểm tra resource và phạm vi dữ liệu; ẩn nút hoặc route guard Angular không thay thế authorization backend.
- `publish`, `delete`, `restore`, `export`, `import` và `force_unlock` tách khỏi `update` khi endpoint thực tế tồn tại.
- Bulk action phải authorize từng resource theo action thực tế; quyền `view` không được dùng để publish hoặc archive dữ liệu.
- Test registry thất bại khi route/UI dùng key không tồn tại, route bảo vệ thiếu permission, hoặc registry có permission mồ côi.

Các permission hệ thống mồ côi đã loại khỏi registry: `audit.export`, `pages.delete`, `pages.restore`, `posts.export`, `products.export`, `transport_requests.*`, `users.export`, `warehouse_requests.*`. Chỉ bổ sung lại khi endpoint và authorization backend thực sự được triển khai.
