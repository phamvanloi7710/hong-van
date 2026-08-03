# Public frontend template porting contract

## Trạng thái

- Prompt nền tảng: P18.
- Nguồn tham chiếu: `FrontEndTemplate/`, chỉ đọc.
- Đích chạy production: Laravel Blade + Vite trong `BackEnd/`.
- Bản kiểm kê này chụp nguồn ngày 2026-08-03; P19 phải kiểm kê lại trước khi port nếu fingerprint thay đổi.

## Inventory nguồn WordPress hiện tại

- 558 file, tổng 45.852.960 byte.
- 266 HTML, 140 JPG, 38 JS, 15 CSS; còn lại là font, PNG, SVG, GIF, XML và một PHP đã tải về.
- Theme: `flatsome`, `web-khoi-nghiep`.
- Plugin: `contact-form-7`, `devvn-quick-buy`, `mega_main_menu`, `woocommerce`, `yith-woocommerce-wishlist`.
- Upload theo năm: `2017`, `2020`.
- `index.html` còn tham chiếu asset WordPress, domain demo, Facebook và CDN ngoài.
- Không tìm thấy package manifest hoặc license riêng đủ để cho phép chạy nguyên nguồn clone.

## Ranh giới nghiệp vụ bắt buộc

Hệ thống Hồng Vân là website doanh nghiệp/catalog nhận yêu cầu báo giá. Không triển khai bán hàng online. P19 và các prompt sau phải loại hoàn toàn:

- WooCommerce và mọi PHP/plugin WordPress.
- Giỏ hàng, checkout, thanh toán, tài khoản mua hàng và wishlist.
- Quick-buy, giá/Offer giả, đơn hàng và hành vi thương mại điện tử.
- External demo link, analytics/tracker của bản mẫu và script không xác định rõ mục đích.

## Cấu trúc đích chuẩn

| Nguồn tham chiếu | Đích Laravel/Vite | Quy tắc |
|---|---|---|
| CSS theme được chọn | `BackEnd/resources/css/public/template/` | Tách token/component, không copy nguyên bundle không kiểm soát |
| JavaScript cần thiết | `BackEnd/resources/js/public/` | Chỉ port hành vi cần thiết, accessible, không cần jQuery/plugin WordPress |
| Ảnh trình bày đã duyệt | `BackEnd/resources/images/public/` | Đổi tên có nghĩa, tối ưu, không mang đường dẫn `uploads/YYYY/MM` |
| Font có quyền sử dụng | `BackEnd/resources/fonts/` | Kiểm tra nguồn/quyền trước khi đưa vào build |
| Header/footer/section | `BackEnd/resources/views/components/public/` | Component Blade có props contract rõ |
| Page template | `BackEnd/resources/views/pages/` | SSR, một H1, không query database trong view |
| Asset runtime | `BackEnd/public/build/` | Chỉ do Vite tạo với filename hash; không commit output build |

Không tạo hoặc giữ các thư mục `wp-admin`, `wp-content`, `wp-includes`, `uploads`, `themes`, `plugins` trong source đích.

## Contract P18 phải được giữ khi port

- Layout gốc: `resources/views/layouts/public.blade.php`.
- Entry Vite: `resources/css/public/app.css` và `resources/js/public/app.js`.
- Token semantic nằm trong `resources/css/public/tokens.css`.
- Primitive Blade: button, link, image qua Media, heading, container, breadcrumbs, alert và form fields.
- Core content phải SSR và vẫn đọc được khi JavaScript bị tắt.
- View chỉ nhận view data/controller; không query database hoặc gọi API loopback.
- Script Vite là file module cùng origin, tương thích CSP `script-src 'self'`; không thêm inline/arbitrary script. Nonce request chỉ dành cho script động được allowlist riêng.
- Mọi text giao diện mới phải có cùng key trong `vi`, `en`, `zh`.
- Preview Page Builder về sau phải dùng cùng layout, component và CSS public.

## Mapping sơ bộ cho P19

| Nhóm trong clone | Hướng port | Page Builder dự kiến |
|---|---|---|
| Header, mega menu | Navigation Blade từ dữ liệu được allowlist | `layout.navigation` |
| Banner/hero | Component media + heading + CTA báo giá | `content.hero` |
| Product grid | Dữ liệu catalog đã publish, không có add-to-cart | `business.product-grid` |
| Service/capability section | Dịch vụ, vận chuyển, kho bãi | `business.service-grid` |
| News section | Post đã publish theo locale | `content.post-list` |
| Contact form | Endpoint lead hiện hữu, validation và consent | `forms.contact` |
| Footer | Settings, branch, contact channel, social link | `layout.footer` |

Mapping chỉ là inventory ban đầu. P19 phải đối chiếu từng section desktop/tablet/mobile trước khi chốt block catalog.

## Asset cache/version

Vite là nguồn duy nhất tạo public asset production. Manifest và filename hash đảm bảo version theo nội dung. `public/.htaccess` đặt cache một năm `immutable` riêng cho `/build/assets/`; `manifest.json` và HTML không dùng cache dài hạn.
