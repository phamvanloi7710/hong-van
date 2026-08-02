# EXTERNAL SOURCE GATES

## Gate A — Admin Template

Path:

```text
Template/
```

Phải READY trước P06. Inventory cần xác định Angular version, layout, routing, theme settings, assets, fonts, icons, auth screens, build commands và license.

Nếu template cũ hơn Angular 22, không nâng trực tiếp source tham chiếu. Port từng phần sang `Admin/` sau khi P05 tạo Angular 22 sạch.

## Gate B — StayHub Media

Path:

```text
SourceIntegrations/StayHubMedia/
```

Nếu thiếu, P17 = `DEFERRED`; P16 vẫn triển khai Media domain contract. Khi source có mặt, phải inventory, xác minh quyền sử dụng, lập feature-parity matrix và port vào Hồng Vân mà không bê tenant/domain/token riêng của StayHub.

## Gate C — Frontend Template

Path chính xác:

```text
FrontEndTemplate/
```

Đường dẫn chuẩn duy nhất là `FrontEndTemplate/`.

Nếu thiếu, P19 = `DEFERRED`; P18 vẫn dựng Blade shell trung tính. Khi source có mặt:

- Inventory HTML/CSS/JS/assets/pages/plugin/license.
- Tách design tokens.
- Port sang Blade layout/component.
- Mapping section → Page Builder block.
- Visual compare desktop/tablet/mobile.
- Không chạy nguyên source tham chiếu làm production app.

## Production gate

Không được P55 cutover nếu Gate B/Gate C còn deferred mà chưa có acceptance chính thức về phạm vi thay thế. Gate A bắt buộc hoàn tất để Admin đúng template yêu cầu.
