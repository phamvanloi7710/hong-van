# ADR-019: Managed technical SEO

- Status: Accepted
- Date: 2026-08-03
- Prompt: P43

## Context

Hồng Vân cần sitemap, `robots.txt`, redirect và structured data có thể quản trị mà không công bố draft, tạo URL không tồn tại, tạo redirect ngoài hệ thống hoặc khai báo dữ liệu/schema giả.

## Decision

- Sitemap dùng cache có version và queue để tạo lại. Sitemap index chỉ liệt kê shard có dữ liệu; shard entity chỉ nhận bản ghi `published`, `robots_index = true` và canonical HTTP/HTTPS đã lưu. Locale đang hoạt động sinh `hreflang`; locale mặc định sinh `x-default`.
- `robots.txt` được render động từ setting `seo_defaults`; file tĩnh mặc định bị loại bỏ để web server không che route Laravel. Khi tắt public indexing, server trả `Disallow: /`.
- Redirect chỉ khớp exact path nội bộ, không nhận URL ngoài, query hoặc fragment. Mã phản hồi chỉ gồm 301, 302 và 410. Server chặn route dành riêng, source trùng và chuỗi vòng lặp trước khi lưu.
- Redirect được giải quyết trong NotFound exception flow thay vì route catch-all, để không giành route thật và không làm sai semantics 404/405.
- Structured data chỉ lấy setting/content thật. `Product` chỉ có `Offer` khi giá fixed/range đang công khai và lớn hơn 0; contact/market/dealer/quantity/giá ẩn không tạo `Offer`.
- JSON-LD dùng JSON hex escaping. Việc chèn schema và breadcrumb vào layout public cuối cùng được hoãn đến khi có frontend template; các builder và Admin preview đã sẵn sàng.

## Consequences

- Publish/unpublish, xóa content và cập nhật SEO metadata làm mất hiệu lực sitemap cache.
- Admin dùng `seo.view` để đọc và `seo.update` để thay đổi redirect, robots hoặc yêu cầu tạo lại sitemap.
- Mọi bảng/cột redirect có comment và tiền tố `hongvan_`; thao tác quản trị được audit.
