# ADR-030: Local runtime riêng, reverse proxy dùng chung

- Status: Accepted
- Date: 2026-08-12

## Context

Nhiều project local cùng cần domain cổng 80 nhưng database, Redis và dữ liệu runtime không được lẫn nhau.

## Decision

- Chỉ dùng chung container `local-proxy` và external network `local-infra`.
- Hồng Vân sở hữu MySQL, Redis, phpMyAdmin, PHP-FPM, queue, scheduler, Nginx và named volumes riêng.
- Chỉ `hongvan-nginx` và `hongvan-phpmyadmin` tham gia `local-infra`; MySQL/Redis không publish cổng host.
- `hongvan.local` được đăng ký động qua `VIRTUAL_HOST`; project không bind cổng 80.
- Migration không chạy tự động khi container khởi động.

## Consequences

Tốn thêm RAM/disk so với hạ tầng database dùng chung, đổi lại dữ liệu, Redis keys, lifecycle và backup của Hồng Vân độc lập với các project khác.
