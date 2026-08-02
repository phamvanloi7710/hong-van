# AGENTS.md — QUY TẮC GỐC CỦA PROJECT HỒNG VÂN

File này áp dụng cho toàn bộ repository. File `AGENTS.md` ở thư mục con có thể bổ sung quy tắc chặt hơn nhưng không được làm yếu các quy tắc gốc.

## 1. Bối cảnh dự án

- Doanh nghiệp: **CÔNG TY TNHH DV VT HỒNG VÂN**.
- Website giới thiệu sản phẩm phân bón, dịch vụ vận chuyển và kho bãi.
- Website public dùng Laravel Blade để ưu tiên SEO.
- Admin dùng Angular mới nhất trong dòng 22.x và template tại `Template/`.
- Backend dùng Laravel 13.x.
- Page Builder trong admin cho phép kéo thả để tùy biến toàn bộ page public.
- Media Manager phải clone từ source StayHub khi source được cung cấp.
- Sản phẩm chỉ để giới thiệu và nhận báo giá; không triển khai checkout/thanh toán.

## 2. Thứ tự đọc bắt buộc

Trước khi sửa code:

1. Đọc file `AGENTS.md` này.
2. Đọc `AGENTS.md` gần nhất trong thư mục đang làm.
3. Đọc prompt hiện tại.
4. Đọc `docs/CODEX_STATE.md`.
5. Chỉ đọc các file liên quan trực tiếp bằng tìm kiếm có mục tiêu.
6. Không quét hoặc đổ toàn bộ repository nếu chưa cần.

## 3. Quy tắc tiết kiệm token

- Dùng `rg`, tìm theo symbol, route, model hoặc tên bảng trước khi mở file.
- Không đọc lại file không đổi nếu đã đủ ngữ cảnh.
- Không in lại toàn bộ file lớn trong báo cáo.
- Chia thay đổi theo đúng phạm vi prompt.
- Không tự chuyển sang prompt kế tiếp.
- Không refactor ngoài phạm vi chỉ vì “tiện tay”.
- Dùng file `docs/CODEX_STATE.md` làm bộ nhớ ngắn hạn của project.
- Ghi quyết định bền vững vào `docs/DECISIONS.md`.
- Khi sửa một hàm hiện hữu, phải đọc và giữ nguyên toàn bộ ngữ cảnh của hàm; không thay bằng đoạn rút gọn hoặc pseudo-code.

## 4. Quy tắc source chỉ đọc

Các thư mục sau là nguồn tham chiếu và mặc định chỉ đọc:

```text
Template/
FrontEndTemplate/
SourceIntegrations/
```

Không format, đổi package, xóa file, sửa asset hoặc commit thay đổi vào các thư mục này nếu prompt không cho phép rõ ràng.

Khi clone giao diện:

- Phải lập inventory trước.
- Tái sử dụng hoặc port vào source đích.
- Không chạy trực tiếp source tham chiếu trong production.
- Không tuyên bố clone chính xác khi source chưa tồn tại hoặc chưa được kiểm tra.

## 5. Database

- Tất cả bảng phải bắt đầu bằng `hongvan_`.
- Không dùng connection-level prefix.
- Model phải khai báo `$table` rõ ràng khi tên không được suy ra an toàn.
- Pivot table, bảng package, queue, cache, session, notification, Sanctum và migrations cũng phải được cấu hình prefix.
- Không tạo bảng không prefix để “sửa sau”.
- Migration phải có index, foreign key, unique constraint và rollback hợp lệ.
- Mọi bảng phải có table comment mô tả mục đích nghiệp vụ hoặc mục đích framework của bảng.
- Mọi cột, kể cả khóa chính, khóa ngoại, pivot, cột framework và cột timestamp, phải có column comment giải thích ý nghĩa và cách sử dụng.
- Migration thêm hoặc đổi cột phải tạo mới hoặc duy trì comment rõ ràng; test database comment bắt buộc phải pass trước khi hoàn tất.
- Dữ liệu thời gian lưu UTC; hiển thị theo `Asia/Ho_Chi_Minh`.
- Không lưu tiền bằng float/double. Dùng decimal hoặc integer phù hợp.

## 6. Kiến trúc Laravel

- Controllers mỏng.
- Validation dùng Form Request.
- Response API dùng Resource/DTO thống nhất.
- Business logic nằm trong Action/Service thuộc domain.
- Authorization dùng Policy/Gate và permission.
- Query phức tạp tách Query Object/Repository khi có lợi rõ ràng.
- Không tạo “BaseRepository” chung chung chỉ để bọc Eloquent.
- Queue cho tác vụ nặng: image variants, email, sitemap, import/export.
- Public Blade không gọi API vòng lại chính ứng dụng.
- Không đặt secret trong code hoặc tài liệu.

## 7. Kiến trúc Angular

- Standalone components.
- Strict TypeScript; không dùng `any` nếu không có giải thích.
- Built-in control flow của Angular hiện đại.
- Lazy-load theo feature.
- Component không gọi HTTP trực tiếp; dùng typed data-access service.
- State cục bộ ưu tiên Signals; state dùng chung phải có ranh giới rõ.
- Giữ nguyên hệ thống layout/theme của template admin.
- Không tự thay template bằng một bộ UI khác.
- Mỗi feature hoàn tất phải chạy lint/test/build liên quan.
- Mọi bước có thay đổi Admin Angular phải chạy production build và sync sang `BackEnd/public/admin/browser` trước khi báo hoàn tất.
- Sau khi sync, phải kiểm tra bundle thực tế qua domain `http://hongvan.local`; chủ dự án nghiệm thu trực tiếp bằng Google Chrome trên domain này.
- Mọi text hiển thị cho người dùng phải dùng translation key; không hardcode trực tiếp trong component/template.
- Mỗi translation key mới phải có đủ `vi`, `en`, `zh` trong cùng thay đổi. Tên riêng doanh nghiệp và technical identifier được giữ nguyên khi phù hợp.
- Favorite menu phải giữ cơ chế chọn nhiều shortcut của Annular template; icon tim luôn đứng bên trái các shortcut đã chọn.

## 8. Page Builder

- Database chỉ lưu document JSON có schema version.
- Không lưu hoặc thực thi PHP, Blade, JavaScript tùy ý từ database.
- Mỗi block phải có type nằm trong allowlist registry.
- Server là nguồn chân lý của schema, validation và renderer.
- Canvas preview trong admin phải dùng chính Blade renderer/CSS của frontend qua iframe hoặc preview session.
- Phiên bản đã publish là immutable.
- Có autosave, draft, publish, schedule, rollback, lock và audit.
- Rich text phải sanitize cả client và server.
- Responsive settings phải có allowlist; không cho nhập CSS tùy ý theo mặc định.

## 9. Sản phẩm và giá

- Không có giỏ hàng, checkout hoặc thanh toán.
- Không hiển thị `0đ`.
- Giá hỗ trợ: fixed, from, range, market, dealer, quantity, contact.
- Giá trống hoặc ẩn phải hiển thị CTA liên hệ báo giá.
- Structured data không khai báo Offer giả khi không có giá công khai hợp lệ.

## 10. Bảo mật

- Admin cùng origin dùng Sanctum cookie/session và CSRF.
- Rate limit endpoint nhạy cảm và form public.
- Upload kiểm tra MIME thực, kích thước, extension, quyền và nội dung ảnh.
- SVG mặc định bị chặn hoặc phải sanitize bằng quy trình riêng.
- Không log password, token, cookie, secret hoặc nội dung nhạy cảm.
- Preview URL phải ký, hết hạn và `noindex`.
- Mọi hành động quản trị quan trọng phải audit.
- Không dùng raw SQL với dữ liệu chưa bind.
- Không tắt CSRF/CORS/security middleware để “cho chạy”.

## 11. Test và chất lượng

Trước khi kết thúc prompt:

- Chạy test đúng phạm vi.
- Chạy formatter/linter.
- Chạy build nếu thay đổi Angular hoặc asset public.
- Kiểm tra migration rollback khi có migration mới.
- Kiểm tra table prefix.
- Ghi lệnh và kết quả vào báo cáo.
- Không báo “đã hoàn tất” khi test chưa chạy; phải ghi rõ lý do.

## 12. Git

- Không force push.
- Không reset/xóa thay đổi chưa commit của người dùng.
- Không commit secret, template có giấy phép hoặc output build nếu policy ignore.
- Mỗi prompt nên tạo một commit nhỏ khi toàn bộ test liên quan pass.
- Không tự push remote trừ khi prompt hoặc chủ dự án yêu cầu rõ.
- Commit message gợi ý: `feat(Pxx): ...`, `fix(Pxx): ...`, `docs(Pxx): ...`.

## 13. Báo cáo cuối mỗi prompt

Bắt buộc gồm:

1. Tóm tắt kết quả.
2. Danh sách file chính đã đổi.
3. Migration/API/UI mới.
4. Lệnh test/build và kết quả.
5. Trạng thái `DONE`, `PARTIAL`, `BLOCKED` hoặc `DEFERRED`.
6. Việc còn lại.
7. Cập nhật `docs/CODEX_STATE.md`.
8. Dừng lại, không chạy prompt sau.
