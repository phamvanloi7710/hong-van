# AGENTS.md — BACKEND TESTS

- Test behavior, permission và contract; không chỉ test implementation.
- Mỗi bug fix có regression test.
- Database test dùng schema `hongvan_*`.
- Không phụ thuộc thứ tự test.
- Factory tạo dữ liệu tối thiểu.
- Test public price behavior gồm null/zero/contact/range/fixed.
- Test Page Builder injection và invalid schema.
