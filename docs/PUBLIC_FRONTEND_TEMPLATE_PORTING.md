# Public frontend template porting contract

## Trạng thái P19

- Nguồn tham chiếu: `FrontEndTemplate/`, chỉ đọc.
- Đích production: Laravel Blade SSR + Vite trong `BackEnd/`.
- Fingerprint cây nguồn ngày 2026-08-03: `c8afab99d6faf61d181abfe7923eec196604d4c9`.
- Inventory: 558 file, 45.852.960 byte; 266 HTML, 140 JPG, 38 JS, 15 CSS và các font/PNG/SVG/GIF/XML liên quan.
- Theme nguồn: `flatsome`, `web-khoi-nghiep`.
- Plugin nguồn: `contact-form-7`, `devvn-quick-buy`, `mega_main_menu`, `woocommerce`, `yith-woocommerce-wishlist`.
- Không tìm thấy license riêng đủ để tái phân phối nguyên bundle hoặc các ảnh thương hiệu/banner của website clone.

## Quyết định port

1. Port ngôn ngữ thiết kế của mẫu: header trắng hai tầng, thanh menu xanh, danh mục dọc, hero ba cột, dải lợi ích, grid card và footer nhiều cột.
2. Không chạy HTML/PHP/JS WordPress trong production; không copy bundle Flatsome, jQuery/plugin cũ hoặc cấu trúc `wp-*`.
3. Không copy logo, banner, ảnh có chữ/thương hiệu/khuyến mại của doanh nghiệp khác. P19 dùng đồ họa CSS đã kiểm soát; Media Manager sẽ cấp ảnh thật ở các prompt binding public.
4. Loại toàn bộ WooCommerce, cart, checkout, payment, account, wishlist, quick-buy, giá/Offer giả và sticky demo bar.
5. Bootstrap, jQuery và Font Awesome Free được chủ dự án yêu cầu bổ sung và tự host qua npm/Vite. Phiên bản P19: Bootstrap `5.3.8`, jQuery `4.0.0`, Font Awesome Free `7.3.1`.
6. Nội dung cốt lõi vẫn SSR. JavaScript chỉ tăng cường menu responsive và cung cấp vendor runtime; menu vẫn đọc được khi JavaScript bị tắt.

## Design tokens

| Nhóm | Token/contract chính | Nguồn cảm hứng từ mẫu |
|---|---|---|
| Màu | `--color-brand`, `--color-brand-strong`, `--color-brand-deep`, `--color-brand-soft` | Xanh lá chủ đạo, menu xanh đậm, nền xanh nhạt |
| Surface | `--color-surface`, `--color-surface-muted`, `--color-surface-dark` | Header/card trắng, nền section xám nhạt, footer tối |
| Typography | `--font-sans`, `--font-display`, thang `--font-size-*` | Sans-serif đậm, heading có độ tương phản cao |
| Khoảng cách | `--space-1` đến `--space-8` | Nhịp 4/8/12/16/24/32/48/72 px |
| Container | `--container-max: 75rem`, `--container-narrow: 52rem` | Khung desktop xấp xỉ 1200 px |
| Breakpoint | 64rem, 48rem, 30rem | Desktop, tablet/mobile navigation, phone nhỏ |
| Component | button, link, heading, alert, form field, catalog/service/content card | Dùng chung cho page và block về sau |

## Layout và page template đã port

- `layouts/public.blade.php`: utility bar, brand, locale, quote CTA, responsive navigation và footer settings-backed.
- `pages/home.blade.php`: category panel, hero, promo cards, benefit strip, catalog groups, services, news empty state và contact/quote CTA.
- `pages/listing.blade.php`: contract listing SSR và empty state.
- `pages/detail.blade.php`: contract detail SSR, nội dung plain string mặc định escaped; chỉ `Htmlable` đã tin cậy mới render HTML.
- `pages/contact.blade.php`: layout thông tin liên hệ và điểm gắn form public về sau.
- `pages/content.blade.php`: layout nội dung tĩnh/CMS với cùng contract HTML an toàn.
- `pages/legal.blade.php`: giữ privacy/terms settings-backed từ P18.

Các page template mới chưa tự mở route/domain nghiệp vụ của P31-P41. Chúng là contract trình bày để các prompt public binding dùng lại, tránh tạo route giả hoặc dữ liệu demo giả.

## Mapping template section → Page Builder block type

| Section từ clone | Blade P19 | Block type dự kiến | Dữ liệu/giới hạn |
|---|---|---|---|
| Utility header | `layouts.public` | `layout.utility-bar` | Settings/contact channel đã public |
| Logo/header | `layouts.public` | `layout.site-header` | Branding Media + company settings |
| Main/mega menu | `layouts.public` | `layout.navigation` | Menu registry P30, không HTML tùy ý |
| Category sidebar | `home` | `business.catalog-navigation` | Taxonomy đã publish |
| Hero slider | `home` | `content.hero` | Heading, text, Media, CTA allowlist; không arbitrary script |
| Side promo banners | `home` | `content.promo-cards` | Internal link/CTA được kiểm tra |
| Benefit strip | `home` | `content.feature-strip` | Icon allowlist + localized text |
| Product carousel/grid | `home`/`listing` | `business.product-grid` | Product đã publish, không add-to-cart/Offer giả |
| Service tiles | `home` | `business.service-grid` | Service/transport/warehouse đã publish |
| Latest posts | `home`/`listing` | `content.post-list` | Post đã publish theo locale |
| Contact/quick-buy form | `contact` | `forms.contact` / `forms.quote-request` | Endpoint lead hiện hữu, consent/rate limit; không quick-buy |
| Content/legal page | `content`/`legal` | `content.rich-text` | Server sanitizer + `Htmlable` trusted contract |
| Footer | `layouts.public` | `layout.footer` | Settings, branches, channels, social links |

## Asset và dependency policy

| Nguồn | Đích | Quy tắc |
|---|---|---|
| CSS được tái hiện | `BackEnd/resources/css/public/` | Token/component/page CSS có kiểm soát, không copy bundle theme |
| JavaScript cần thiết | `BackEnd/resources/js/public/app.js` | Vite module, same-origin, không plugin WordPress |
| Ảnh trình bày được duyệt | `BackEnd/resources/images/public/` | Tên có nghĩa, không giữ `uploads/YYYY/MM`; P19 chưa copy ảnh nguồn do license/nội dung |
| Font icon | npm `@fortawesome/fontawesome-free` | Vite hash, chỉ solid/regular webfont đang dùng |
| Asset runtime | `BackEnd/public/build/` | Chỉ do Vite tạo; output bị ignore và không commit |

Không tạo `wp-admin`, `wp-content`, `wp-includes`, `uploads`, `themes` hoặc `plugins` trong source đích.

## Visual compare

| Viewport | Nguồn | Kết quả P19 có chủ đích |
|---|---|---|
| Desktop 1440×900 | Category trái, hero giữa, hai promo phải, menu xanh | Giữ cấu trúc và nhịp; thay banner/demo brand bằng CSS art Hồng Vân an toàn |
| Tablet 768×1024 | Nội dung co về tablet, menu cần gọn | Hero trước, category hai cột, navigation hamburger; không overflow |
| Mobile 390×844 | Một cột | Hero, category, promo và card xếp dọc; menu accessible; không overflow |

Runtime UAT tại `hongvan.local` xác nhận `vi/en/zh`, đúng một H1, core text SSR, anchor nội bộ hoạt động, Font Awesome load sau `document.fonts.ready` và `scrollWidth = clientWidth` ở tablet/mobile.

## Phần cố ý deferred

- Dữ liệu catalog/post/service thật và route detail/listing: P31, P33-P41.
- Media/logo/banner thật: sau khi nội dung và Media ID được chủ dự án duyệt.
- Form contact/quote có submit: binding public P25/P38.
- Menu quản trị/Page Builder: P21-P30.
- Theme Studio: P20.
