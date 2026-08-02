# SEO REQUIREMENTS

## Public rendering

- Laravel Blade SSR.
- HTML có nội dung ngay trong response.
- Không phụ thuộc JavaScript để thấy nội dung cốt lõi.
- URL sạch, ổn định và theo locale nếu bật đa ngôn ngữ.

## Mỗi page/entity có thể cấu hình

- SEO title.
- Meta description.
- Canonical URL.
- Robots.
- Open Graph title/description/image.
- Twitter card.
- Breadcrumb.
- Structured data phù hợp.
- Redirect khi đổi slug.

## Product

- Không khai báo Offer với giá 0 hoặc giá giả.
- Chỉ đưa `price` vào structured data khi có giá công khai, xác định và đúng currency.
- Khi contact/market/dealer, dùng Product schema không Offer hoặc cấu trúc phù hợp được kiểm chứng.
- Product page có heading, mô tả, thông số, công dụng, hướng dẫn, FAQ và sản phẩm liên quan khi dữ liệu có thật.

## Technical SEO

- Sitemap index.
- Sitemap page/product/post/service/crop/project.
- Robots.txt quản trị.
- Canonical.
- Hreflang nếu đa ngôn ngữ.
- 301 redirect.
- 404/410.
- Noindex preview, admin, search nội bộ không phù hợp.
- BreadcrumbList.
- Organization/LocalBusiness theo dữ liệu thật.
- WebSite/SearchAction chỉ khi search public hoạt động đúng.
- Article cho bài viết.
- ImageObject khi phù hợp.
- Core Web Vitals.
- Alt text bắt buộc ở luồng xuất bản nội dung quan trọng.

## Page Builder

SEO metadata nằm ngoài document blocks để query và validate dễ dàng. Block heading phải kiểm tra cấu trúc H1/H2; mặc định một H1 chính mỗi page.
