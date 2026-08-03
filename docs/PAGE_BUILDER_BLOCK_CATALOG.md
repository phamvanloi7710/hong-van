# PAGE BUILDER BLOCK CATALOG

## Layout blocks — P22

Các block trong catalog này được đăng ký cố định ở server và render bằng Laravel Blade. Document chỉ lưu `type`, props và các token có trong allowlist; không nhận Blade view, class PHP, CSS selector, raw CSS hoặc JavaScript từ database.

Mọi block dùng chung `visibility.desktop|tablet|mobile`. Responsive style chỉ nhận spacing scale `none|xs|sm|md|lg|xl|2xl|3xl` và các preset liệt kê dưới đây. Độ sâu toàn document tối đa 12; từng layout block giới hạn ở depth 8, riêng `Section` chỉ được đặt ở root/depth 1.

| Type | Mục đích | Props | Responsive style | Parent | Children | Số con / Blade view |
| --- | --- | --- | --- | --- | --- | --- |
| `layout.section` | Vùng nội dung semantic cấp trang | `background`: `transparent|surface|surface-muted|brand|brand-soft|gradient-brand|media`; `backgroundMediaId`; `ariaLabel` | `paddingY`, `paddingX`, `align` | root | Container, Stack, Grid, Columns, Spacer, Divider, Placeholder | 0–100 / `section.blade.php` |
| `layout.container` | Giới hạn chiều rộng nội dung | `width`: `narrow|default|wide|full` | `paddingX` | Section, Stack, Grid, Columns | Stack, Grid, Columns, Spacer, Divider, Placeholder | 0–100 / `container.blade.php` |
| `layout.stack` | Xếp nội dung theo hàng/cột | `wrap` | `gap`, `direction`: `vertical|horizontal`, `align`, `justify` | Section, Container, Grid, Columns | Container, Grid, Columns, Spacer, Divider, Placeholder | 0–100 / `stack.blade.php` |
| `layout.grid` | Lưới card theo preset | — | `columns`: desktop/tablet `1–4`, mobile `1–2`; `gap`, `align` | Section, Container, Stack | Container, Stack, Spacer, Divider, Placeholder | 1–12 / `grid.blade.php` |
| `layout.columns` | Bố cục cột/sidebar | `desktopPreset`: `equal-2|equal-3|equal-4|sidebar-left|sidebar-right`; `tabletPreset`: `equal-2|stack`; mobile luôn `stack` | `gap`, `align` | Section, Container, Stack | Container, Stack, Spacer, Divider, Placeholder | đúng 2, 3 hoặc 4 theo desktop preset / `columns.blade.php` |
| `layout.spacer` | Khoảng trống theo spacing token | — | `size` | layout parent hợp lệ | không có | 0 / `spacer.blade.php` |
| `layout.divider` | Đường phân tách semantic | `variant`: `solid|dashed`; `color`: `border|brand|muted` | `marginY` | layout parent hợp lệ | không có | 0 / `divider.blade.php` |

`align` chỉ nhận `start|center|end|stretch`; `justify` chỉ nhận `start|center|end|between`. Background media chỉ nhận public ULID và renderer tự tạo route media cố định. Gradient dùng theme token `brand`, không nhận chuỗi gradient tùy ý.

Contract chung: version `1`, category `layout`, `bindings` rỗng, cache tags `page-builder` và `page-builder:layout`, chưa cần block migration. `Section` hỗ trợ `ariaLabel`; `Divider` dùng `<hr aria-hidden="true">`; `Spacer` không mang nội dung; các wrapper còn lại không tự tạo heading hoặc landmark sai ngữ nghĩa. Chỉ `Section` dùng media và chỉ qua public media route. Coverage nằm tại `LayoutBlockTest`: registry metadata, fixture render, escaping, nesting, raw CSS, mobile Grid/Columns và media ID.

Ví dụ giá trị allowlisted cho từng block:

```text
Section:   props={background:brand-soft,ariaLabel:Giới thiệu}; desktop={paddingY:2xl,paddingX:none,align:stretch}
Container: props={width:default}; mobile={paddingX:none}
Stack:     props={wrap:false}; mobile={gap:md,direction:vertical,align:stretch,justify:start}
Grid:      props={}; mobile={columns:1,gap:md,align:stretch}
Columns:   props={desktopPreset:sidebar-left,tabletPreset:stack,mobilePreset:stack}; mobile={gap:md,align:stretch}
Spacer:    props={}; mobile={size:lg}
Divider:   props={variant:solid,color:border}; mobile={marginY:md}
```

## Ví dụ document

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
      "id": "section-home-0001",
      "type": "layout.section",
      "version": 1,
      "props": { "background": "surface", "ariaLabel": "Giới thiệu" },
      "style": {
        "desktop": { "paddingY": "2xl", "paddingX": "none", "align": "stretch" },
        "tablet": { "paddingY": "xl", "paddingX": "none", "align": "stretch" },
        "mobile": { "paddingY": "lg", "paddingX": "none", "align": "stretch" }
      },
      "visibility": { "desktop": true, "tablet": true, "mobile": true },
      "bindings": {},
      "children": [
        {
          "id": "container-home-0001",
          "type": "layout.container",
          "version": 1,
          "props": { "width": "default" },
          "style": {
            "desktop": { "paddingX": "none" },
            "tablet": { "paddingX": "none" },
            "mobile": { "paddingX": "none" }
          },
          "visibility": { "desktop": true, "tablet": true, "mobile": true },
          "bindings": {},
          "children": []
        }
      ]
    }
  ]
}
```

Fixture tổng hợp cho preview/test nằm ở `App\Domain\PageBuilder\LayoutPreviewFixture` và chứa đủ cả 7 layout block. Tabs/Accordion chưa được thêm ở P22 vì contract behavior/ARIA của template chưa sẵn sàng; chúng không phải điều kiện bắt buộc của prompt này.
