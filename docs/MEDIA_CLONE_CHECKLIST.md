# MEDIA MANAGER — CHECKLIST CLONE STAYHUB

Trang tham chiếu: `stayhub.local/media`.

Source thật sẽ được đặt trong:

```text
SourceIntegrations/StayHubMedia/
```

## Quy tắc

- Không suy đoán UI từ URL.
- Không screenshot/OCR thay cho source khi source code được cung cấp.
- Lập inventory route, component, service, API, model, quyền, asset và shortcut.
- Nguồn tham chiếu chỉ đọc.
- Tách hành vi cần clone và chi tiết phụ thuộc riêng StayHub.
- Tạo mapping từ source sang architecture Hồng Vân.

## Chức năng cần kiểm tra khi source có mặt

- Tree folder.
- Breadcrumb.
- Grid/list mode.
- Upload click/drag/drop.
- Upload nhiều file.
- Progress và lỗi từng file.
- Search.
- Filter loại file, ngày, dung lượng, người upload.
- Sort.
- Preview.
- Metadata: tên, alt, caption, title, description.
- Tag.
- Move/copy.
- Rename.
- Replace file.
- Crop/resize/focal point.
- Image variants.
- Download.
- Copy URL.
- Chọn media từ dialog.
- Multi-select.
- Bulk move/delete.
- Trash/restore/permanent delete.
- Used-by references.
- Quyền theo role.
- Keyboard shortcuts.
- Responsive behavior.
- Empty/loading/error state.
- Storage local/S3.
- Audit.

## Bảo mật

- MIME sniffing.
- Extension allowlist.
- Max size theo loại.
- Image decode/re-encode nếu cần.
- SVG block hoặc sanitize.
- Không phục vụ file thực thi.
- Filename không tin cậy.
- Storage path không nhận trực tiếp từ client.
- Signed/private URL khi cần.
- Quota và rate limit.
- Virus scan hook có thể bật.

## P17 feature parity matrix

Status definitions:

- `EXACT`: cùng khả năng và luồng chính; API được ánh xạ sang domain Hồng Vân.
- `ADAPTED`: giữ mục tiêu/nghiệp vụ nhưng thay chi tiết để phù hợp storage, RBAC hoặc Annular shell của Hồng Vân.
- `NOT_APPLICABLE`: source không có hoặc không phù hợp contract/bảo mật đã duyệt; lý do bắt buộc ghi rõ.

| Capability | Source evidence | Hồng Vân P17 | Parity | Lý do |
|---|---|---|---|---|
| Toolbar, tree, breadcrumb, grid/list | `media-library.html/.ts/.scss` | `Admin/src/app/features/media/media-page.*` | EXACT behavior / ADAPTED visual | Giữ theme Annular Hồng Vân, không copy branding/asset StayHub |
| Click/drag/drop, multi-file queue, per-file progress/error | `queueFiles`, `confirmUpload`, drag handlers | Upload panel + `uploadWithProgress` | EXACT | MIME/path vẫn do backend Hồng Vân xác thực/sinh |
| Upload từ URL | `uploadFromUrl`, SSRF test nguồn | Không expose | NOT APPLICABLE | P16 chỉ duyệt multipart Admin; tránh mở thêm remote-fetch/SSRF surface trong P17 |
| Search/type/status/visibility/trash/sort | filters + `MediaApi.list` | Allowlist request/query + Admin filters | ADAPTED | Target thêm lifecycle/trash/visibility; không mang scope tenant/property |
| Folder create/rename/lock và navigation | folder APIs + dialogs | Relational folder tree, dialog, parent breadcrumb, lock | EXACT workflow / ADAPTED model | Source dùng directory path; target dùng `public_id` + parent FK |
| Folder move/copy/delete | clipboard action items | File move; folder hierarchy giữ ổn định | NOT APPLICABLE | Không cho recursive copy/delete vượt usage/trash policy đã duyệt |
| Multi/range selection và bulk actions | select/range/select-all, move/lock/visibility/delete/download | Ctrl/Shift select, select-all, bulk move/lock/visibility/trash/restore/download | ADAPTED | Backend permission vẫn kiểm tra từng resource; download nhiều file theo từng file, không ZIP |
| Lock file/folder | `setLock`, ancestor lock guards | `is_locked` trên hai bảng, middleware/policy/service guard | EXACT | Hai cột có index/comment/rollback |
| Public/private | `setVisibility` di chuyển disk | API cập nhật visibility intent | ADAPTED | Hồng Vân không làm lộ path/URL cố định và không đổi disk chỉ vì UI action |
| Preview/download/copy URL | private/text preview, download, `copyUrl` | Same-origin authenticated preview/download | ADAPTED | Không copy URL tĩnh vì storage driver có thể đổi |
| Metadata panel | rename/title/alt/description | title/alt/caption + MIME/size/dimensions/status | EXACT | Dùng contract P16 |
| Image editor | crop/rotate/flip/filter/preset, save new | crop pointer, rotate, flip, brightness/contrast/grayscale, resize, save new | ADAPTED | Bổ sung resize; không thay file gốc và đi lại upload validation |
| Replace file | Không có endpoint/flow replace trong source | Không triển khai | NOT APPLICABLE | Source editor cũng lưu thành file mới |
| Used-by | Không có registry tương đương trong source UI | `hongvan_media_usages` trong detail panel | ADAPTED extension | Giữ safe-delete contract P16 |
| Trash/restore/permanent delete | Source có delete trực tiếp/bulk | Target trash/restore/permanent delete có usage guard | ADAPTED extension | An toàn hơn và đúng lifecycle P16 |
| Picker single/multiple + MIME | `media-picker.ts` | Shared `MediaPickerContract` dialog | EXACT | Dùng cho settings/page/product về sau |
| Keyboard/accessibility | HostListener shortcuts, overlays | Ctrl/Command+A, U, G/L, Delete, Escape, Enter, Arrow; ARIA/listbox/dialog | ADAPTED | Giữ các shortcut cốt lõi, bỏ shortcut phụ thuộc clipboard folder nguồn |
| Empty/loading/error/responsive | source templates/styles | Loading spinner, retry error, empty CTA, desktop/mobile breakpoints | EXACT | Được lint accessibility và visual baseline |
| Local/S3 abstraction, MIME, extension, size, decode, SVG | source config/service | P16 `MediaStorage` + `MediaUploadInspector` | EXACT security intent | Không bê disk/path nguồn |
| Permission và audit | source four permission keys/audit | `media.view|create|update|delete|restore`, Policy + middleware + AuditTrail | EXACT / ADAPTED naming | `media.upload` nguồn ánh xạ `media.create` |
| E2E/visual regression | Không có artifact được port | `Admin/e2e/media.spec.ts` + committed snapshot | ADAPTED extension | Chạy Chrome tại `hongvan.local` với API fixture ổn định |

## Evidence

- Relevant source baseline: 33 files, aggregate SHA-256 `4931202da73a46e869d3a533e68cdeddf3e041ad846e6b72b5254943c236023c`.
- Backend contract test: `BackEnd/tests/Feature/Media/MediaFoundationTest.php`.
- Admin data test: `Admin/src/app/features/media/media-data.service.spec.ts`.
- E2E + visual: `Admin/e2e/media.spec.ts`, `Admin/e2e/media.spec.ts-snapshots/media-library-main-win32.png`.
