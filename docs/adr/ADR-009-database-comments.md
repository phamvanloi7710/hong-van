# ADR-009 — Bắt buộc comment cho mọi bảng và cột

**Status:** Accepted

**Date:** 2026-08-02

## Context

Schema cần tự mô tả để người vận hành và lập trình viên hiểu đúng mục đích của từng bảng, từng cột ngay trong công cụ quản trị MySQL. Các migration framework mặc định không cung cấp đầy đủ comment.

## Decision

Mọi migration tạo bảng phải khai báo table comment. Mọi cột mới hoặc thay đổi, bao gồm khóa chính, khóa ngoại, pivot, timestamp và bảng framework, phải có column comment giải thích ý nghĩa và cách sử dụng. Bảng `hongvan_migrations` do Laravel tạo trước migration được bổ sung comment trong migration nền tảng đầu tiên.

Test kiến trúc truy vấn `information_schema` phải thất bại nếu tồn tại bảng hoặc cột có comment rỗng.

## Consequences

- Schema MySQL có thể được hiểu trực tiếp mà không phải dò ngược toàn bộ source.
- Mỗi thay đổi schema phải cập nhật comment cùng lúc với migration.
- Migration fresh, rollback và test kiến trúc comment là quality gate bắt buộc.
