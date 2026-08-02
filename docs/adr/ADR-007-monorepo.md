# ADR-007 — Monorepo cho toàn bộ nền tảng

**Status:** Accepted

**Date:** 2026-08-02

## Context

Nền tảng gồm Laravel backend/public Blade, Angular Admin, tài liệu, prompt tuần tự, script, hạ tầng và các source tham chiếu. Các phần có contract chặt và thường thay đổi trong cùng checkpoint.

## Decision

Dùng một repository root với các vùng chính: `BackEnd/`, `Admin/`, `docs/`, `prompts/`, `scripts/`, `docker/` và các thư mục reference read-only. Contract API/schema/permission và tài liệu liên quan được thay đổi nguyên tử trong cùng prompt/commit khi có thể.

## Consequences

- Dễ đồng bộ backend, Admin, Blade, migration, test và tài liệu.
- CI phải tách job theo phạm vi để tránh build toàn bộ khi không cần.
- Build output không được xem như source; chỉ pipeline hợp lệ mới đồng bộ Admin build vào Laravel public path.
- Quy tắc thư mục và ownership phải rõ để không đưa licensed reference source vào commit/deploy.
