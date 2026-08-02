# ADR-003 — Prefix bảng được khai báo tường minh

**Status:** Accepted

**Date:** 2026-08-02

## Context

Dự án yêu cầu mọi bảng, kể cả bảng framework/package, có prefix `hongvan_`. Connection-level prefix làm tên bảng phụ thuộc runtime config và khiến migration/model/package khó audit.

## Decision

Mọi migration phải ghi đầy đủ tên bảng `hongvan_*`; không cấu hình prefix ở database connection. Model phải khai báo `$table` khi Eloquent không thể suy ra an toàn. Pivot, queue, cache, session, notification, Sanctum và package migrations phải được cấu hình hoặc publish để dùng tên có prefix.

## Consequences

- Schema và migration thể hiện chính xác tên vật lý, dễ review và kiểm tra tự động.
- Tên bảng dài và cấu hình package chi tiết hơn.
- CI phải scan `Schema::create`, `Schema::table`, model `$table` và package migrations.
- Migration fresh và rollback theo batch phải pass trước khi merge thay đổi schema.
