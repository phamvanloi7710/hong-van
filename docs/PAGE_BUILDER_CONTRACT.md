# PAGE BUILDER CONTRACT

## Trạng thái triển khai

P21 đã triển khai foundation backend. P22–P25 sẽ bổ sung block thực tế, P26 triển khai editor Angular, P27 preview, P28 version/publish, P29 reusable/global blocks, P30 template và P31 public renderer. Foundation hiện không tự nhận Blade view/class, không `eval` và không lưu mã thực thi trong database.

## PageDocument schema v1

```json
{
  "schemaVersion": 1,
  "themeVersionId": null,
  "pageSettings": {
    "container": "default",
    "background": "surface",
    "hideHeader": false,
    "hideFooter": false
  },
  "blocks": [
    {
      "id": "block-home-0001",
      "type": "foundation.placeholder",
      "version": 1,
      "props": { "label": "" },
      "style": { "desktop": {}, "tablet": {}, "mobile": {} },
      "visibility": { "desktop": true, "tablet": true, "mobile": true },
      "bindings": {},
      "children": []
    }
  ]
}
```

`PageDocumentSchema` là nguồn chân lý cho version và giới hạn: tối đa 512 KiB, độ sâu 12 và 300 block. Root, `pageSettings` và từng block chỉ nhận field trong allowlist. `themeVersionId` chỉ nhận ULID hoặc `null`.

## BlockRegistry phía server

Mỗi `BlockDefinition` khai báo đầy đủ:

- `type`, version, nhãn `vi|en|zh`, category, icon và thumbnail.
- schema riêng cho `props`, `style`, `visibility`, `bindings` và defaults.
- quyền đặt ở root, parent/children hợp lệ.
- data dependencies, permission và cache tags.
- renderer class cố định trong code, sanitizer, migration tuần tự và test fixture.

API chỉ trả metadata an toàn; không trả renderer/sanitizer class, namespace, Blade view hoặc path nội bộ. P21 chỉ đăng ký `foundation.placeholder` làm fixture nền móng. Layout/content/media/business/form block thuộc P22–P25.

## Validation và migration

`PageDocumentValidator`:

- trả lỗi theo path như `document.blocks.0.props.label`;
- từ chối block type/version không có trong registry, field tùy ý và quan hệ parent/child sai;
- phát hiện duplicate block ID, binding tham chiếu không tồn tại và cycle;
- từ chối Blade, PHP, script, `javascript:`, HTML event handler và `data:text/html`;
- áp dụng sanitizer của block trước khi document được lưu;
- tính checksum SHA-256 trên document canonical.

`PageDocumentMigrator` chỉ nâng block tuần tự `n -> n+1`; import thiếu migration, version tương lai hoặc schema không hỗ trợ bị từ chối trước khi lưu.

## Database và version contract

P21 tạo các bảng có prefix/comment đầy đủ:

```text
hongvan_pages
hongvan_page_translations
hongvan_page_versions
hongvan_page_publish_schedules
hongvan_page_locks
hongvan_page_templates
hongvan_page_template_versions
hongvan_page_preview_sessions
```

`document_json` chỉ chứa PageDocument đã validate. `hongvan_pages` trỏ tới draft mutable và published version. Model chặn update/delete một version đã có status `published`; publish/rollback ở P28 phải tạo lịch sử mới thay vì sửa document đã công bố. Lock và preview chỉ lưu hash token, không lưu raw token.

## API P21

Tất cả endpoint dùng Sanctum, permission `pages.*`, `PagePolicy`, response contract chung và ID public:

```text
GET  /api/admin/v1/page-builder/registry
GET  /api/admin/v1/page-builder/pages
POST /api/admin/v1/page-builder/pages
GET  /api/admin/v1/page-builder/pages/{public_id}
PUT  /api/admin/v1/page-builder/pages/{public_id}
PUT  /api/admin/v1/page-builder/pages/{public_id}/draft
```

P21 chỉ cung cấp CRUD metadata và lưu draft document. Chưa có publish, schedule, lock API, preview iframe hoặc editor Angular.

## Cache contract

Published cache key:

```text
page-builder:published:{pagePublicId}:{locale}:{pageVersionPublicId}:{themeVersionPublicId|theme-none}
```

Logical tags gồm `page-builder`, `page:{publicId}`, `page-version:{publicId}` và `theme-version:{publicId}`. P31 renderer phải dùng đúng key/tag này và invalidate khi page version, theme version hoặc data dependency thay đổi.

## Preview và Angular canvas (deferred)

P27 sẽ dùng signed expiring URL, owner check, Redis TTL, `noindex`, CSP chặt và `postMessage` với origin allowlist. Angular canvas dùng iframe của cùng Blade renderer/CSS public; không dựng một renderer HTML khác ở client.

## Điều kiện an toàn đã kiểm chứng ở P21

1. Database không lưu Blade/PHP/view name.
2. Unknown block, arbitrary `view`, script payload, duplicate ID, invalid child, excessive depth và cycle đều bị từ chối.
3. Registry API typed và không lộ class/path nội bộ.
4. Published PageVersion bất biến ở model contract.
5. Migration block bắt buộc tuần tự trước validation.
