# ADR-001 — Laravel Blade cho public website

**Status:** Accepted

**Date:** 2026-08-02

## Context

Website public cần SEO tốt, HTML có sẵn khi crawler truy cập, tải nhanh và dùng trực tiếp dữ liệu/domain service của Laravel. Angular được dành cho Admin SPA, không phải public frontend.

## Decision

Public website được render server-side bằng Laravel Blade trong `BackEnd/resources/views/`. Public route gọi application/domain service nội bộ, không gọi HTTP vòng lại chính Laravel. JavaScript chỉ tăng cường tương tác cần thiết, không trở thành nguồn render chính.

## Consequences

- SEO metadata, canonical, structured data, sitemap và error page có thể render phía server.
- Public frontend và backend dùng chung authorization, cache và domain logic mà không tạo API loopback.
- Template public chỉ được inventory rồi port có kiểm soát vào Blade; không chạy production trực tiếp từ `FrontEndTemplate/`.
- Các tương tác phức tạp phải được thiết kế theo progressive enhancement và kiểm tra khi JavaScript chậm hoặc bị tắt.
