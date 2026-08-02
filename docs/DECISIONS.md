# ARCHITECTURE DECISIONS

## ADR-001 — Public site dùng Laravel Blade

**Status:** Accepted

Lý do: SEO, HTML server-rendered, tốc độ và tích hợp trực tiếp với Laravel.

## ADR-002 — Admin dùng Angular tách source

**Status:** Accepted

Source tại `Admin/`, production build được đồng bộ vào `BackEnd/public/admin/browser/`.

## ADR-003 — Table prefix ghi rõ trong migration

**Status:** Accepted

Không dùng connection-level prefix. Tất cả bảng là `hongvan_*`.

## ADR-004 — Page Builder preview dùng Blade iframe

**Status:** Accepted

Admin canvas sử dụng renderer thật của public để tránh lệch style.

## ADR-005 — Không có e-commerce

**Status:** Accepted

Catalog + CTA báo giá; không cart, checkout, payment.

## ADR-006 — External source là read-only

**Status:** Accepted

`Template/`, `FrontEndTemplate/`, `SourceIntegrations/` không được sửa.
