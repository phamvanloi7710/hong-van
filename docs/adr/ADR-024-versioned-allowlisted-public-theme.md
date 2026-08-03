# ADR-024 — Versioned allowlisted public theme

- Status: Accepted
- Date: 2026-08-03

## Decision

Theme website public được tách khỏi skin Admin và lưu theo hai lớp `hongvan_themes` / `hongvan_theme_versions`. Mỗi version chỉ chứa document token theo allowlist; server biên dịch token thành CSS custom properties bằng mapping cố định. Hệ thống không nhận selector, CSS hoặc JavaScript tùy ý.

Draft, signed preview, publish và rollback dùng cùng `ThemeCssCompiler`. Version đã publish không bị sửa; public runtime chỉ đọc version được trỏ bởi theme active và cache theo version. Publish/rollback phải xóa cache và ghi audit.

## Consequences

- Preview phản ánh đúng renderer/CSS của public Blade và luôn có URL ký, hết hạn, `noindex`.
- Rollback chỉ đổi con trỏ published và tạo draft mới từ version lịch sử, không xóa lịch sử.
- Thêm token mới bắt buộc cập nhật schema validation, compiler, Admin typed control và test injection/version/cache.
