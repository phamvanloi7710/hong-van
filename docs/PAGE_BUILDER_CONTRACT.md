# PAGE BUILDER CONTRACT

## Mục tiêu

Admin kéo thả để tạo và chỉnh sửa mọi page public. Preview phải đúng style ngoài frontend.

## Nguyên tắc quan trọng nhất

**Angular không tự dựng một bản HTML giả lập riêng cho canvas.**

Canvas sử dụng iframe chứa cùng Blade renderer và CSS mà public page sử dụng. Angular chỉ điều khiển document, selection, drag/drop, property panel và gửi phiên preview.

## Document schema đề xuất

```json
{
  "schemaVersion": 1,
  "themeVersionId": "01...",
  "pageSettings": {
    "container": "default",
    "background": "surface",
    "hideHeader": false,
    "hideFooter": false
  },
  "blocks": [
    {
      "id": "01...",
      "type": "hero.split",
      "version": 1,
      "props": {},
      "style": {
        "desktop": {},
        "tablet": {},
        "mobile": {}
      },
      "visibility": {
        "desktop": true,
        "tablet": true,
        "mobile": true
      },
      "bindings": {},
      "children": []
    }
  ]
}
```

## Registry

Mỗi block registry entry phải khai báo:

- `type`.
- `version`.
- Nhãn và nhóm.
- Icon/thumbnail.
- Schema props.
- Giá trị mặc định.
- Schema style responsive.
- Allowed children/parent.
- Data source/binding được phép.
- Blade view cố định.
- Permission cần thiết.
- Migration từ block version cũ.
- Sanitization.
- Cache tags.
- Test fixtures.

Không được dùng tên Blade view lấy trực tiếp từ database.

## Nhóm block

### Layout

- Section.
- Container.
- Grid.
- Columns.
- Stack.
- Spacer.
- Divider.
- Tabs/accordion nếu frontend template có.

### Content

- Heading.
- Rich text.
- Button.
- Icon.
- List.
- Table.
- Quote.
- Badge.
- Card.
- FAQ.

### Media

- Image.
- Gallery.
- Video embed allowlist.
- Background media.
- Image-text split.
- Logo cloud.

### Business

- Hero.
- Product categories.
- Product grid/list.
- Featured products.
- Crop solution grid.
- Services.
- Fleet.
- Transport routes.
- Warehouses.
- Company statistics.
- Partners.
- Certifications.
- Projects/case studies.
- Posts/news.
- Breadcrumb.
- CTA.

### Forms

- Contact.
- Product quote.
- Transport request.
- Warehouse request.
- Newsletter only if scope is approved.

## Preview session

Đề xuất:

```text
POST /api/admin/v1/page-builder/preview-sessions
PUT  /api/admin/v1/page-builder/preview-sessions/{token}
GET  /preview/page-builder/{signedToken}
```

- Session tạm lưu Redis.
- Token ký, hết hạn.
- Preview response có `noindex`, CSP chặt.
- Iframe và Angular giao tiếp bằng `postMessage` với origin allowlist.
- Debounce update.
- Không ghi version DB cho từng phím gõ.

## Versioning

- Draft mutable thông qua autosave.
- Khi save milestone, tạo immutable version.
- Publish trỏ `published_version_id`.
- Scheduled publish qua scheduler/queue.
- Rollback tạo version mới từ version cũ, không sửa lịch sử.
- Audit đầy đủ.

## An toàn

- Rich text sanitize server-side.
- Không cho script.
- Không cho event handler HTML.
- Không cho CSS tùy ý mặc định.
- Video chỉ từ provider allowlist.
- Link protocol allowlist.
- Dynamic query parameters allowlist.
- Binding chỉ đến data source registry.
- Giới hạn depth, số block, payload size.
- Validate cycle/recursive children.
- Import JSON phải qua schema migration và validation.

## Hiệu năng

- Cache published document và rendered fragment.
- Cache key gồm page, locale, published version, theme version.
- Media dùng variant phù hợp.
- Không query N+1 trong dynamic blocks.
- Eager load theo block data dependency.
- Invalidate cache theo tags khi entity thay đổi.

## Điều kiện nghiệm thu cốt lõi

1. Cùng một document cho preview và public tạo markup tương đương.
2. Block không hợp lệ bị từ chối với lỗi chỉ rõ path.
3. Publish/rollback không mất lịch sử.
4. Không thể inject Blade/PHP/JS.
5. Responsive preview đúng breakpoint của frontend.
6. Theme token đổi trong admin phản ánh ra preview và public.
