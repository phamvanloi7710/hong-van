# ADR-005 — Không triển khai e-commerce

**Status:** Accepted

**Date:** 2026-08-02

## Context

Mục tiêu kinh doanh là giới thiệu phân bón, vận chuyển, kho bãi và tiếp nhận lead/yêu cầu báo giá. Cart, checkout và thanh toán tạo thêm tồn kho, đơn hàng, tài chính và rủi ro bảo mật ngoài phạm vi.

## Decision

Sản phẩm là catalog có CTA liên hệ/báo giá. Giá hỗ trợ các mode `fixed`, `from`, `range`, `market`, `dealer`, `quantity`, `contact`; giá trống hoặc ẩn không hiển thị `0đ`. Không tạo cart, checkout, payment, order hoặc Offer giả trong structured data.

## Consequences

- Phạm vi tập trung vào CMS, catalog, SEO, Page Builder, lead/CRM nhẹ và dịch vụ doanh nghiệp.
- Quote request có thể chứa danh sách sản phẩm nhưng không mang semantics của order/checkout.
- Yêu cầu e-commerce trong tương lai cần change request và ADR mới, không mở rộng ngầm từ module Products.
