# AGENTS.md — BACKEND

Áp dụng cho toàn bộ `BackEnd/`.

- Laravel 13.x, PHP 8.5.x.
- Public Blade và admin API cùng một Laravel application.
- API admin namespace `/api/admin/v1`.
- Controllers mỏng; Form Request + Action/Service + Resource.
- Tất cả bảng `hongvan_*`.
- Không dùng package tạo bảng mặc định chưa được publish/chỉnh prefix.
- Không dùng Inertia/Livewire cho public hoặc admin nếu chưa có change request.
- Public Blade không gọi HTTP loopback.
- N+1 bị cấm; dùng eager loading có chủ đích.
- Mọi query từ sort/filter request phải allowlist.
- Tác vụ ảnh/email/export/import/sitemap nặng dùng queue.
- Mỗi endpoint có authorization và test.
- Sau thay đổi: chạy test phạm vi, Pint và static analysis liên quan.
