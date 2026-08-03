# P46 Accessibility, responsive và performance baseline

Ngày đo: 2026-08-03. Môi trường: Windows, WAMP, `http://hongvan.local`, Chrome cài trên máy, Angular production build và Laravel Vite production build.

## Phạm vi đã kiểm tra

- Admin shell/dashboard/login và workflow quản trị người dùng, cài đặt.
- Landmark, heading, accessible name, trạng thái loading/error, keyboard focus, skip-link, chart text alternative và reduced motion.
- Responsive dashboard ở 390x844, 768x1024 và 1440x900.
- Angular lazy routes và production bundle budgets.
- Laravel Vite public asset budgets trên neutral placeholder hiện có.
- Cache hiện hữu: dashboard aggregate 60 giây và sitemap 15 phút; không tạo cache giả cho public page chưa tồn tại.

## Baseline đo được

| Hạng mục | Kết quả production |
| --- | --- |
| Admin initial bundle | 123.39 kB raw, 27.04 kB estimated transfer |
| Admin largest lazy chunk | 256.83 kB raw, 72.49 kB estimated transfer |
| Public CSS | 54.31 kB raw, 11.43 kB gzip |
| Public JS | 48.62 kB raw, 18.65 kB gzip |
| Responsive overflow | Không overflow ngang ở cả 3 viewport đã test |
| Keyboard | Tab đầu tiên vào skip-link; Enter chuyển focus tới `#admin-main` |
| Semantic heading | Một H1 của shell; dashboard bắt đầu từ H2 |

Budget gate:

- Angular: initial warning/error 500 kB/1 MB, component style 11/12 kB, any script 300/400 kB.
- Public Vite: mỗi CSS tối đa 160 KiB, JS 150 KiB, ảnh AVIF/WebP/PNG/JPEG 500 KiB.
- `BackEnd/npm run build` tự chạy budget và fail khi vượt ngưỡng.

## Ngoại lệ được ghi nhận

- Lighthouse không có trong dependency/toolchain hiện tại. Lệnh `npx --no-install lighthouse --version` dừng vì package không tồn tại; không cài package trái quy định và không tạo điểm số giả.
- Public frontend cuối cùng, public forms/gallery/page blocks và Page Builder editor/preview chưa tồn tại do P18-P31 được chủ dự án chủ động để cuối cùng chờ template. Vì vậy chưa thể đo Core Web Vitals hay chạy axe/Lighthouse đại diện cho các trang này.
- Neutral Laravel welcome hiện tại render SSR và nội dung cốt lõi sử dụng được khi tắt JavaScript, nhưng không được xem là public frontend cuối cùng.

Các ngoại lệ trên phải được đóng trong giai đoạn frontend cuối và trước UAT/production. P46 hiện hoàn tất phần Admin cùng performance gate, còn phần public/Page Builder ở trạng thái deferred theo quyết định của chủ dự án.
