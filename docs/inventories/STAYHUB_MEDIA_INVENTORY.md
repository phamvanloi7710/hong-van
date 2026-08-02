# STAYHUB MEDIA INVENTORY

## Status

`READY — RE-INVENTORIED FOR P17`

Source read-only: `SourceIntegrations/StayHubMedia/`.

P01 ghi nhận source còn thiếu nên không có hash cũ để so sánh byte-for-byte. Tại P17, source do chủ dự án bổ sung đã được đọc lại từ đầu; 33 file Media liên quan được lập baseline SHA-256 tổng hợp:

```text
4931202da73a46e869d3a533e68cdeddf3e041ad846e6b72b5254943c236023c
```

Hash trên được tính từ đường dẫn tương đối và SHA-256 từng file thuộc hai phạm vi:

- `BackEnd/app/Modules/Media/**`, config, hai migration Media và `tests/Feature/MediaApiTest.php`.
- `FrontEnd/src/app/features/media/**`.

Toàn bộ source tham chiếu vẫn bị Git ignore theo policy và không được sửa/format/copy nguyên app.

## Backend inventory

- Controller/API: list/upload/show/content/download, folder list/create/rename, upload URL, move/copy, delete, lock, visibility và download ZIP.
- Service chính: `MediaService.php`; có kiểm tra scope organization/property, khóa tổ tiên, MIME thực, remote URL/redirect/public IP và audit.
- Model: `MediaFile`, `MediaFolderLock`.
- Permission nguồn: `media.view`, `media.upload`, `media.update`, `media.delete`.
- Test nguồn: upload/list/move/download/delete, folder lock/copy, visibility private, ZIP, SSRF, property scope và permission.

## Frontend inventory

- `media-library`: toolbar, tree/breadcrumb, grid/list, filter/sort, history navigation, multi-select/range select, upload queue/drag-drop/URL, folder dialog, clipboard move/copy, lock, visibility, download, metadata và preview.
- `media-picker`: wrapper dùng chung, single/multi selection và MIME filter.
- `media-image-editor`: crop bằng pointer, rotate, flip, zoom/filter/preset/compare và lưu thành file mới.
- Keyboard: Ctrl/Command+A, Delete, Escape, U, view shortcuts, Enter và Arrow navigation.
- Trạng thái: loading, empty, error, private/text preview và overlay close.

## Mapping Hồng Vân

- Organization/property/tenant, domain và token StayHub không được port.
- Permission nguồn `media.upload` ánh xạ sang `media.create`; các action còn lại dùng `media.view|update|delete|restore` và backend policy/middleware hiện hữu.
- Folder path vật lý nguồn ánh xạ sang `hongvan_media_folders` quan hệ cha/con; storage path Hồng Vân vẫn do server sinh.
- Visual giữ shell/theme Annular Hồng Vân; clone luồng và khả năng, không copy branding/asset StayHub.
- Chi tiết exact/adapted/not-applicable nằm tại `docs/MEDIA_CLONE_CHECKLIST.md`.

## Gate

`stayhub_media_gate: READY_PORTED_P17`.
