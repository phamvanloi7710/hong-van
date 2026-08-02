# HỒNG VÂN — FULL CODEX PROMPT KIT V2

Bộ tài liệu dùng để xây dựng tuần tự website cho:

> **CÔNG TY TNHH DV VT HỒNG VÂN**

## Phạm vi

- Catalog giới thiệu sản phẩm phân bón, có thể hiển thị giá hoặc liên hệ báo giá.
- Giới thiệu dịch vụ vận chuyển và hệ thống kho bãi.
- Tiếp nhận liên hệ, yêu cầu báo giá, yêu cầu vận chuyển và yêu cầu thuê kho.
- Laravel Blade SSR cho website public để ưu tiên SEO.
- Angular cho trang quản trị, sử dụng template tham chiếu tại `Template/`.
- Page Builder kéo thả toàn bộ page public; preview dùng chính Blade renderer/CSS public.
- Media Manager có domain nền và được clone theo source StayHub khi source được cung cấp.
- Theme admin lưu riêng theo từng user.
- Mọi bảng do project tạo phải có tiền tố `hongvan_`.

## Quy ước thư mục V2

```text
Template/                          Template Angular Admin, read-only
FrontEndTemplate/                  Template website public, read-only
SourceIntegrations/StayHubMedia/   Source tham chiếu Media Manager, read-only
Admin/                             Source Angular Admin chính thức
BackEnd/                           Laravel API + Blade public
```

Template website public phải đặt tại `FrontEndTemplate/`.

## Baseline công nghệ

- Laravel 13.x.
- PHP 8.5.x.
- Angular 22.1.x.
- Node.js 24.x tương thích Angular 22.
- MySQL 8.4 LTS.
- Redis cho cache, queue, rate limit và preview session.
- Nginx + PHP-FPM cho production.

Patch version phải được P04/P05 kiểm tra lại bằng nguồn chính thức trước khi bootstrap. Không tự nâng major ngoài baseline nếu chưa có ADR và kiểm thử.

## Bắt đầu

1. Đọc `START_HERE.md`.
2. Đọc `docs/IMPLEMENTATION_GUIDE_FROM_SCRATCH.md`.
3. Đặt template Admin vào `Template/`.
4. Đặt template public vào `FrontEndTemplate/`.
5. Mở đúng root project bằng Codex.
6. Chạy từng prompt từ P00 đến P56, mỗi prompt là một checkpoint độc lập.

## Tài liệu chính

```text
START_HERE.md                                      Hướng dẫn bắt đầu nhanh
HUONG_DAN_TRIEN_KHAI_TU_DAU.md                    Hướng dẫn từ local đến production
DANH_SACH_PROMPT_CHI_TIET.md                      Danh sách tuần tự P00–P56 kèm nghiệm thu
docs/IMPLEMENTATION_GUIDE_FROM_SCRATCH.md         Bản sao hướng dẫn trong thư mục docs
prompts/PROMPT_INDEX.md                           Bảng tra cứu nhanh 57 prompt
prompts/DANH_SACH_PROMPT_CHI_TIET_00_56.md        Danh sách chi tiết trong thư mục prompts
prompts/FULL_PROMPT_SEQUENCE.md                   Toàn bộ nội dung 57 prompt
prompts/RUN_PROMPT_TEMPLATE.md                    Mẫu giao từng prompt cho Codex
```

## Quy tắc bất biến

- Không chạy tất cả prompt trong một lượt.
- Không sửa source tham chiếu.
- Không tự thêm cart, checkout, payment hoặc order workflow.
- Không hiển thị `0đ`; giá trống chuyển thành liên hệ báo giá.
- Không lưu hoặc thực thi Blade/PHP/JavaScript tùy ý từ database.
- Không dùng connection-level table prefix; tên bảng phải ghi rõ `hongvan_`.
- Không commit secret, `.env`, upload thật hoặc dữ liệu production.
