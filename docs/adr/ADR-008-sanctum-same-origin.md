# ADR-008 — Sanctum same-origin cookie/session cho Admin

**Status:** Accepted

**Date:** 2026-08-02

## Context

Admin SPA và Laravel được phục vụ cùng origin. Lưu bearer token trong browser storage tăng rủi ro token theft và không phù hợp security baseline của dự án.

## Decision

Admin xác thực bằng Laravel Sanctum cookie/session cùng origin và CSRF. Session phải regenerate sau login; cookie dùng `Secure`, `HttpOnly`, `SameSite` phù hợp môi trường. Angular gửi credential và lấy CSRF cookie theo contract, không lưu access token trong `localStorage` hoặc `sessionStorage`. Laravel middleware, Policy/Gate và permission luôn enforce server-side.

## Consequences

- Giảm bề mặt lộ bearer token trong JavaScript/browser storage.
- Local, staging và production phải cấu hình domain, HTTPS, cookie và CSRF nhất quán.
- Endpoint login/logout/session và interceptor lỗi cần test với 401, 403, 419 và session expiry.
- Nếu sau này cần API token cho client khác, phải có contract và ADR riêng, không tái sử dụng ngầm flow Admin.
