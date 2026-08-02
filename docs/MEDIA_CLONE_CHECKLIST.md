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
