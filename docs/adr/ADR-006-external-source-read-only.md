# ADR-006 — External source là read-only

**Status:** Accepted

**Date:** 2026-08-02

## Context

`Template/`, `FrontEndTemplate/` và `SourceIntegrations/` chứa source tham chiếu có thể có license, cấu trúc, dependency và domain riêng. Sửa trực tiếp làm mất baseline và khó đối chiếu khi source được thay thế.

## Decision

Ba vùng source tham chiếu luôn read-only. Mỗi source phải được inventory bằng manifest/file thật, lập mapping port/drop/decision rồi mới copy chọn lọc vào `Admin/` hoặc `BackEnd/`. Không cài dependency, format, build production, xóa asset hoặc sửa source tham chiếu nếu không có prompt cho phép rõ ràng.

## Consequences

- Baseline và license boundary được giữ nguyên.
- Mọi adaptation sống trong source đích và có test riêng.
- Khi người dùng thay source tham chiếu, inventory liên quan phải chạy lại từ đầu.
- Source thiếu được đánh dấu `DEFERRED — SOURCE MISSING`; không được tưởng tượng chức năng hoặc giao diện.
