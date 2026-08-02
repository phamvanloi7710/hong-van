# ADR-004 — Page Builder preview bằng Blade iframe

**Status:** Accepted

**Date:** 2026-08-02

## Context

Nếu Angular tự mô phỏng markup/CSS public, preview dễ lệch so với trang thật và tạo hai renderer phải duy trì. Page Builder cần preview đúng theme, breakpoint và dynamic block của public website.

## Decision

Canvas chính của Angular dùng iframe tải preview session từ Laravel. Iframe render bằng cùng Blade renderer và CSS của public frontend. Preview URL phải ký, có thời hạn, `noindex`, CSP chặt; giao tiếp `postMessage` chỉ chấp nhận origin và message schema trong allowlist.

## Consequences

- Preview và public dùng một nguồn markup/style, giảm sai lệch.
- Cần quản lý session tạm, debounce, cache invalidation, signed URL và lifecycle iframe.
- Selection/drag/drop/property editor nằm ở Angular; schema validation và render vẫn do server quyết định.
- Test phải so sánh output preview/public và kiểm tra origin/message giả mạo.
