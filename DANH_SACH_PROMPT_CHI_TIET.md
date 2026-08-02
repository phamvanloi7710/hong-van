# DANH SÁCH PROMPT CHI TIẾT P00–P56 — HỒNG VÂN V2

Frontend template path: `FrontEndTemplate/`.

## P00 — THIẾT LẬP BASELINE VÀ KIỂM KÊ REPOSITORY
- **File:** `prompts/00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định.
- **Điều kiện tiên quyết:**
1. Bộ prompt đã được giải nén tại root project.
2. Codex được mở ở đúng root có `AGENTS.md`.
- **Checklist nghiệm thu:**
- [ ] Không có file source nào bị chỉnh ngoài `docs/CODEX_STATE.md` và báo cáo P00.
- [ ] Trạng thái Git và công cụ được ghi đúng từ lệnh thực tế.
- [ ] Ba external source gate có trạng thái rõ: READY, MISSING hoặc INVALID.
- [ ] `docs/CODEX_STATE.md` đặt `last_completed_prompt: 00` và `next_prompt: 01_EXTERNAL_SOURCE_INVENTORY`.

## P01 — KIỂM KÊ TEMPLATE VÀ EXTERNAL SOURCE
- **File:** `prompts/01_EXTERNAL_SOURCE_INVENTORY.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source.
- **Điều kiện tiên quyết:**
1. Prompt 00 DONE.
2. `docs/CODEX_STATE.md` phản ánh source gates.
- **Checklist nghiệm thu:**
- [ ] Không có thay đổi bên trong source read-only.
- [ ] Inventory dùng bằng chứng từ manifest/file thật.
- [ ] Source thiếu được đánh dấu deferred, không bị báo lỗi toàn project.
- [ ] Có mapping rõ giữa source và `Admin/`/`BackEnd/resources/`/Media domain.

## P02 — CHỐT ADR, MODULE MAP VÀ KẾ HOẠCH BÀN GIAO
- **File:** `prompts/02_ARCHITECTURE_RECORDS_AND_DELIVERY_PLAN.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng.
- **Điều kiện tiên quyết:**
1. P00–P01 DONE hoặc source gate đã được đánh dấu deferred.
- **Checklist nghiệm thu:**
- [ ] Mỗi ADR có Context, Decision, Consequences, Status, Date.
- [ ] Module map không biến website thành ERP/WMS/TMS/e-commerce.
- [ ] Definition of Done áp dụng được cho backend và Angular.
- [ ] Không có thông tin công ty giả.

## P03 — THIẾT LẬP REPOSITORY HYGIENE VÀ BASELINE CÔNG CỤ
- **File:** `prompts/03_REPOSITORY_HYGIENE_AND_DEVELOPER_BASELINE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework.
- **Điều kiện tiên quyết:**
1. P02 DONE.
- **Checklist nghiệm thu:**
- [ ] Scripts fail-fast, không in secret, hỗ trợ path có khoảng trắng.
- [ ] Ignore không vô tình ignore `AGENTS.md` hoặc prompt/docs.
- [ ] Không commit build output hoặc source template theo mặc định.
- [ ] Baseline scripts chạy được trên shell hiện tại hoặc ghi rõ chưa test shell khác.

## P04 — KHỞI TẠO LARAVEL 13 BACKEND
- **File:** `prompts/04_BOOTSTRAP_LARAVEL_13_BACKEND.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Khởi tạo Laravel 13 sạch trong `BackEnd/`, pin PHP 8.5 và chuẩn bị nền tảng Blade/API mà không phá AGENTS hiện có.
- **Điều kiện tiên quyết:**
1. P03 DONE.
2. PHP/Composer tương thích hoặc blocker đã được giải quyết.
- **Checklist nghiệm thu:**
- [ ] `php artisan --version` là Laravel 13.x.
- [ ] `composer.json` yêu cầu PHP ^8.5 hoặc constraint tương thích quyết định đã ghi.
- [ ] Trang welcome/health và test smoke hoạt động.
- [ ] Không có starter kit SPA ngoài phạm vi.
- [ ] Không mất AGENTS/guidelines.

## P05 — KHỞI TẠO ANGULAR 22.1 ADMIN
- **File:** `prompts/05_BOOTSTRAP_ANGULAR_22_ADMIN.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready.
- **Điều kiện tiên quyết:**
1. P04 DONE.
2. Node/npm tương thích Angular 22.
- **Checklist nghiệm thu:**
- [ ] `ng version` báo Angular/CLI 22.1.x cùng dòng.
- [ ] `npm ci`, test và build mặc định pass.
- [ ] Strict mode bật.
- [ ] Không dùng NgModule architecture cũ nếu CLI không cần.
- [ ] AGENTS ở feature vẫn còn.

## P06 — PORT TEMPLATE ADMIN VÀO ANGULAR 22
- **File:** `prompts/06_PORT_ADMIN_TEMPLATE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu.
- **Điều kiện tiên quyết:**
1. P05 DONE.
2. Gate Admin Template = READY; nếu thiếu thì BLOCKED, không deferred toàn project.
- **Checklist nghiệm thu:**
- [ ] Source `Template/` có diff bằng 0.
- [ ] Admin build pass Angular 22.
- [ ] Layout desktop/mobile tương đồng template.
- [ ] Theme panel cơ bản hoạt động tạm.
- [ ] Không kéo theo API/demo backend hoặc secret của template.

## P07 — TÍCH HỢP BUILD ANGULAR VÀO LARAVEL
- **File:** `prompts/07_INTEGRATE_ADMIN_BUILD_WITH_LARAVEL.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`.
- **Điều kiện tiên quyết:**
1. P06 DONE.
- **Checklist nghiệm thu:**
- [ ] `npm run build:laravel` tạo đúng output.
- [ ] Không chỉnh thủ công output build.
- [ ] Refresh một deep link admin không 404.
- [ ] Public Laravel route không bị admin catch-all chiếm.
- [ ] Source map production theo policy.

## P08 — XÂY NỀN DATABASE VÀ CƯỠNG CHẾ TIỀN TỐ
- **File:** `prompts/08_DATABASE_FOUNDATION_AND_PREFIX_ENFORCEMENT.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix.
- **Điều kiện tiên quyết:**
1. P04 DONE.
2. MySQL test database sẵn sàng hoặc SQLite không được dùng để che khác biệt MySQL.
- **Checklist nghiệm thu:**
- [ ] Database fresh chỉ có bảng `hongvan_*` do project tạo.
- [ ] Không double-prefix.
- [ ] Script prefix bắt được fixture sai trong test.
- [ ] Migrations rollback sạch.
- [ ] Model/core config trỏ đúng bảng.

## P09 — XÂY NỀN API ADMIN V1
- **File:** `prompts/09_ADMIN_API_FOUNDATION.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module.
- **Điều kiện tiên quyết:**
1. P08 DONE.
- **Checklist nghiệm thu:**
- [ ] Response contract nhất quán.
- [ ] Status code đúng.
- [ ] Production response không lộ stack trace.
- [ ] Sort injection bị từ chối.
- [ ] Request ID xuất hiện trong response/log context.

## P10 — XÁC THỰC ADMIN BẰNG SANCTUM COOKIE/SESSION
- **File:** `prompts/10_AUTHENTICATION_SANCTUM_SESSION_CSRF.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin.
- **Điều kiện tiên quyết:**
1. P07–P09 DONE.
- **Checklist nghiệm thu:**
- [ ] Login Angular hoạt động bằng cookie + CSRF.
- [ ] Refresh giữ session hợp lệ.
- [ ] Logout vô hiệu session.
- [ ] Inactive/locked user bị chặn.
- [ ] Không có token nhạy cảm trong localStorage/log.

## P11 — QUẢN LÝ NGƯỜI DÙNG, VAI TRÒ VÀ QUYỀN
- **File:** `prompts/11_RBAC_USERS_ROLES_PERMISSIONS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default.
- **Điều kiện tiên quyết:**
1. P10 DONE.
- **Checklist nghiệm thu:**
- [ ] User không quyền nhận 403 dù gọi API trực tiếp.
- [ ] UI phản ánh quyền sau refresh.
- [ ] Không thể làm mất Super Admin cuối cùng.
- [ ] Tất cả bảng prefix.
- [ ] Permission seed idempotent.

## P12 — LƯU THEME ADMIN THEO TỪNG USER
- **File:** `prompts/12_PER_USER_ADMIN_THEME_PREFERENCES.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn.
- **Điều kiện tiên quyết:**
1. P06, P10 DONE.
- **Checklist nghiệm thu:**
- [ ] Theme tồn tại qua logout/login và thiết bị khác.
- [ ] User A không đọc/sửa user B.
- [ ] Invalid token bị reject.
- [ ] Fallback hoạt động khi preference lỗi.
- [ ] Build pass.

## P13 — THIẾT LẬP THÔNG TIN CÔNG TY VÀ CẤU HÌNH TOÀN CỤC
- **File:** `prompts/13_COMPANY_SETTINGS_BRANCHES_CONTACT_CHANNELS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp.
- **Điều kiện tiên quyết:**
1. P11 DONE.
2. Core settings tables P08 tồn tại.
- **Checklist nghiệm thu:**
- [ ] Thông tin công ty chỉnh được từ admin.
- [ ] Không hardcode contact trong Blade/Angular.
- [ ] Secret không lộ qua API/log.
- [ ] Cache cập nhật ngay sau save.
- [ ] Data chưa có để trống có validation phù hợp.

## P14 — ĐA NGÔN NGỮ VÀ TIMEZONE
- **File:** `prompts/14_LOCALIZATION_AND_TIMEZONE_FOUNDATION.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Thiết lập tiếng Việt mặc định, tiếng Anh sẵn sàng bật, translation-table conventions và hiển thị giờ Việt Nam.
- **Điều kiện tiên quyết:**
1. P13 DONE.
- **Checklist nghiệm thu:**
- [ ] VI hoạt động đầy đủ.
- [ ] EN disabled không tạo broken route.
- [ ] Không trộn translation JSON với bảng nếu cần query.
- [ ] Timezone conversion có test boundary.

## P15 — NHẬT KÝ HOẠT ĐỘNG VÀ HARDENING NỀN
- **File:** `prompts/15_AUDIT_LOG_AND_SECURITY_FOUNDATION.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây audit trail, security headers, rate limiting và redaction trước các module nội dung.
- **Điều kiện tiên quyết:**
1. P10–P14 DONE.
- **Checklist nghiệm thu:**
- [ ] Thao tác nhạy cảm tạo audit.
- [ ] Audit không chứa secret.
- [ ] Admin thường không sửa/xóa log.
- [ ] Preview iframe vẫn hoạt động theo frame/CSP design.
- [ ] Headers test pass.

## P16 — XÂY DOMAIN MEDIA ĐỘC LẬP UI
- **File:** `prompts/16_MEDIA_DOMAIN_FOUNDATION.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo hạ tầng media an toàn, API contract và picker interface để các module dùng ngay, trước khi clone UI StayHub.
- **Điều kiện tiên quyết:**
1. P13–P15 DONE.
- **Checklist nghiệm thu:**
- [ ] Upload file hợp lệ thành công và file nguy hiểm bị từ chối.
- [ ] Delete media đang dùng có cảnh báo/policy.
- [ ] Variant chạy queue và failure được ghi.
- [ ] API typed và permission test.
- [ ] UI tối thiểu chọn được media cho module sau.

## P17 — CLONE MEDIA MANAGER TỪ STAYHUB
- **File:** `prompts/17_CLONE_STAYHUB_MEDIA_MANAGER.md`
- **Cờ:** `DEFERRED_ALLOWED`
- **Mục tiêu:** Clone chính xác chức năng và trải nghiệm trang media tham chiếu vào Hồng Vân, trên domain/API an toàn đã có.
- **Điều kiện tiên quyết:**
1. P16 DONE.
2. Gate StayHub Media = READY. Nếu source thiếu: cập nhật DEFERRED và dừng prompt này, không fail prompt khác.
- **Checklist nghiệm thu:**
- [ ] Source read-only không có diff.
- [ ] Parity matrix có bằng chứng.
- [ ] Luồng upload/search/folder/select/trash/restore hoạt động.
- [ ] Permission backend bảo vệ mọi action.
- [ ] Không còn label/domain StayHub ngoài tài liệu attribution/mapping.

## P18 — KHỞI TẠO FRONTEND PUBLIC BẰNG LARAVEL BLADE
- **File:** `prompts/18_BLADE_PUBLIC_FRONTEND_FOUNDATION.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo shell Blade SSR, asset pipeline, layout và component primitives trung tính để không phụ thuộc FrontEndTemplate chưa có.
- **Điều kiện tiên quyết:**
1. P04, P13–P16 DONE.
- **Checklist nghiệm thu:**
- [ ] Home server-rendered.
- [ ] Không có SPA public.
- [ ] View không query DB trực tiếp.
- [ ] Components dùng design tokens.
- [ ] FrontEndTemplate chưa có vẫn build/run được mà không giả là final design.

## P19 — PORT FRONTEND TEMPLATE PUBLIC VÀO LARAVEL BLADE
- **File:** `prompts/19_PORT_PUBLIC_FRONTEND_TEMPLATE.md`
- **Cờ:** `DEFERRED_ALLOWED`
- **Mục tiêu:** Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder.
- **Điều kiện tiên quyết:**
1. P18 DONE.
2. Gate FrontEndTemplate = READY. Nếu source thiếu: DEFERRED và dừng prompt này.
- **Checklist nghiệm thu:**
- [ ] Source FrontEndTemplate diff = 0.
- [ ] Blade output đạt visual fidelity.
- [ ] Core content vẫn SSR.
- [ ] Không có broken asset/external demo link.
- [ ] Design tokens rõ và dùng chung với block.

## P20 — THEME STUDIO CHO WEBSITE PUBLIC
- **File:** `prompts/20_PUBLIC_THEME_STUDIO.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin.
- **Điều kiện tiên quyết:**
1. P18 DONE; P19 có thể DONE hoặc DEFERRED.
- **Checklist nghiệm thu:**
- [ ] Theme public tách khỏi user admin theme.
- [ ] Published pages dùng published theme version.
- [ ] Rollback không mất lịch sử.
- [ ] Không inject CSS/JS tùy ý.
- [ ] Preview và public dùng cùng token compiler.

## P21 — PAGE BUILDER SCHEMA VÀ BLOCK REGISTRY
- **File:** `prompts/21_PAGE_BUILDER_DOCUMENT_SCHEMA_AND_REGISTRY.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây lõi document, block registry, validation, migrations và API metadata phía server.
- **Điều kiện tiên quyết:**
1. P18, P20 DONE.
- **Checklist nghiệm thu:**
- [ ] Database không lưu Blade/PHP.
- [ ] Unknown block bị reject.
- [ ] Published version model immutable contract.
- [ ] Registry API typed.
- [ ] Security tests pass.

## P22 — BLOCK LAYOUT NỀN
- **File:** `prompts/22_PAGE_BUILDER_LAYOUT_BLOCKS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens.
- **Điều kiện tiên quyết:**
1. P21 DONE.
- **Checklist nghiệm thu:**
- [ ] Layout blocks render trong public Blade.
- [ ] Không arbitrary CSS.
- [ ] Nested constraints rõ.
- [ ] Mobile behavior test.
- [ ] Preview fixture tạo được.

## P23 — BLOCK NỘI DUNG VÀ MEDIA
- **File:** `prompts/23_PAGE_BUILDER_CONTENT_AND_MEDIA_BLOCKS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Triển khai block nội dung phổ biến và media, với sanitization và accessibility.
- **Điều kiện tiên quyết:**
1. P22 DONE.
2. P16 Media DONE.
- **Checklist nghiệm thu:**
- [ ] XSS payload bị loại/reject.
- [ ] Media usage được ghi khi document save/publish.
- [ ] Alt/decorative validation.
- [ ] Markup accessible.
- [ ] No N+1 media queries.

## P24 — BLOCK DỮ LIỆU NGHIỆP VỤ
- **File:** `prompts/24_PAGE_BUILDER_DYNAMIC_BUSINESS_BLOCKS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý.
- **Điều kiện tiên quyết:**
1. P21–P23 DONE. Domain chưa tồn tại có thể dùng data-source contracts/fakes, sau module tương ứng hoàn thiện adapter.
- **Checklist nghiệm thu:**
- [ ] Không query tùy ý từ page document.
- [ ] Chỉ entity published xuất hiện public.
- [ ] Empty data không phá layout.
- [ ] Cache invalidation contract rõ.
- [ ] Dynamic block query không N+1.

## P25 — BLOCK FORM VÀ CTA TẠO LEAD
- **File:** `prompts/25_PAGE_BUILDER_FORM_BLOCKS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility.
- **Điều kiện tiên quyết:**
1. P21–P23 DONE. Lead domain có thể dùng contract, hoàn thiện persistence ở P38.
- **Checklist nghiệm thu:**
- [ ] Arbitrary field/action bị reject.
- [ ] Form keyboard/screen-reader usable.
- [ ] Rate limit/honeypot hoạt động.
- [ ] Context product không thể bị giả mạo mà không validate.
- [ ] No synchronous slow email.

## P26 — EDITOR KÉO THẢ PAGE BUILDER TRONG ANGULAR
- **File:** `prompts/26_ANGULAR_PAGE_BUILDER_EDITOR.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo.
- **Điều kiện tiên quyết:**
1. P21 registry API DONE.
2. P06 admin template DONE.
- **Checklist nghiệm thu:**
- [ ] Editor build pass.
- [ ] Không dùng `any` cho document core.
- [ ] Invalid nesting bị chặn UI và vẫn được server reject.
- [ ] Undo/redo ổn định.
- [ ] UI đúng admin template.

## P27 — LIVE PREVIEW BẰNG BLADE IFRAME
- **File:** `prompts/27_BLADE_IFRAME_LIVE_PREVIEW.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Bảo đảm canvas admin render đúng markup/CSS public thông qua preview session ký và Redis.
- **Điều kiện tiên quyết:**
1. P26 DONE.
2. P21–P25 renderers DONE.
- **Checklist nghiệm thu:**
- [ ] Canvas và public dùng cùng renderer.
- [ ] Preview URL không truy cập được sau expiry hoặc user khác.
- [ ] Không ghi DB quá mức.
- [ ] postMessage an toàn.
- [ ] Responsive preview đúng CSS breakpoint.

## P28 — VERSIONING VÀ XUẤT BẢN PAGE
- **File:** `prompts/28_PAGE_VERSIONING_AUTOSAVE_PUBLISH_SCHEDULE_ROLLBACK.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation.
- **Điều kiện tiên quyết:**
1. P27 DONE.
- **Checklist nghiệm thu:**
- [ ] Published version không bị update.
- [ ] Concurrent edit không silent overwrite.
- [ ] Schedule đúng timezone.
- [ ] Cache invalidated.
- [ ] Audit đầy đủ.

## P29 — PAGE TEMPLATES, IMPORT/EXPORT VÀ EDIT LOCKS
- **File:** `prompts/29_PAGE_TEMPLATES_IMPORT_EXPORT_AND_EDIT_LOCKS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật.
- **Điều kiện tiên quyết:**
1. P28 DONE.
- **Checklist nghiệm thu:**
- [ ] Import không thực thi code.
- [ ] Template/version immutable hợp lý.
- [ ] Lock không làm mất nội dung.
- [ ] Force unlock restricted.
- [ ] Export có thể round-trip.

## P30 — MENU, HEADER, FOOTER VÀ GLOBAL REGIONS
- **File:** `prompts/30_MENUS_HEADER_FOOTER_GLOBAL_REGIONS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning.
- **Điều kiện tiên quyết:**
1. P28 DONE.
- **Checklist nghiệm thu:**
- [ ] Menu quản trị kéo thả được.
- [ ] Header/footer public lấy dữ liệu version published.
- [ ] Không hardcode navigation.
- [ ] Accessible mobile navigation.
- [ ] Region preview đúng frontend style.

## P31 — ROUTING PUBLIC VÀ CÁC TRANG LÕI
- **File:** `prompts/31_PUBLIC_ROUTING_CORE_PAGES_AND_ERROR_PAGES.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản.
- **Điều kiện tiên quyết:**
1. P28–P30 DONE.
- **Checklist nghiệm thu:**
- [ ] Page publish truy cập được SSR.
- [ ] Draft không lộ.
- [ ] Reserved path không bị page chiếm.
- [ ] Canonical/redirect đúng.
- [ ] Error pages an toàn.

## P32 — DOMAIN SẢN PHẨM PHÂN BÓN
- **File:** `prompts/32_PRODUCT_DOMAIN_AND_TAXONOMY.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây schema/domain sản phẩm, danh mục, thương hiệu, thuộc tính, media và pricing modes đúng website giới thiệu.
- **Điều kiện tiên quyết:**
1. P14, P16 DONE.
- **Checklist nghiệm thu:**
- [ ] Tất cả bảng prefix.
- [ ] Price resolver đúng yêu cầu.
- [ ] Không có e-commerce schema.
- [ ] Translations và slugs hoạt động.
- [ ] Migration fresh/rollback pass.

## P33 — QUẢN TRỊ SẢN PHẨM VÀ CATALOG PUBLIC
- **File:** `prompts/33_PRODUCT_ADMIN_AND_PUBLIC_CATALOG.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm.
- **Điều kiện tiên quyết:**
1. P32 DONE.
2. P16 Media, P31 public routing DONE.
- **Checklist nghiệm thu:**
- [ ] Admin CRUD đầy đủ.
- [ ] Public chỉ thấy published.
- [ ] Price/contact đúng mọi mode.
- [ ] CTA gửi đúng product ID an toàn.
- [ ] No N+1.

## P34 — GIẢI PHÁP THEO CÂY TRỒNG
- **File:** `prompts/34_CROP_SOLUTIONS_MODULE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO.
- **Điều kiện tiên quyết:**
1. P33 DONE.
- **Checklist nghiệm thu:**
- [ ] Giải pháp có thể quản trị hoàn toàn.
- [ ] Public SSR và SEO-ready.
- [ ] Không hardcode kiến thức giả.
- [ ] Liên kết sản phẩm ổn khi entity archive.

## P35 — MODULE DỊCH VỤ CHUNG
- **File:** `prompts/35_SERVICES_MODULE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Quản trị dịch vụ công ty ngoài các entity vận chuyển/kho chuyên biệt và render public.
- **Điều kiện tiên quyết:**
1. P31 DONE.
- **Checklist nghiệm thu:**
- [ ] Service module không chồng dữ liệu transport/warehouse.
- [ ] Admin/public hoạt động.
- [ ] Dynamic block adapter hoàn chỉnh.

## P36 — VẬN CHUYỂN, ĐỘI XE VÀ TUYẾN
- **File:** `prompts/36_TRANSPORTATION_FLEET_ROUTES_MODULE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây module giới thiệu năng lực vận chuyển và nhận yêu cầu, không biến thành TMS điều phối.
- **Điều kiện tiên quyết:**
1. P31, P35 DONE.
- **Checklist nghiệm thu:**
- [ ] Không có TMS scope creep.
- [ ] Public thể hiện năng lực.
- [ ] Transport request context hợp lệ.
- [ ] Dynamic blocks dùng published data.

## P37 — KHO BÃI VÀ YÊU CẦU THUÊ KHO
- **File:** `prompts/37_WAREHOUSES_MODULE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS.
- **Điều kiện tiên quyết:**
1. P31, P35 DONE.
- **Checklist nghiệm thu:**
- [ ] Không có WMS scope creep.
- [ ] Kho public hiển thị từ data thật.
- [ ] Map không hardcode key.
- [ ] Request context an toàn.

## P38 — LEAD, BÁO GIÁ VÀ QUY TRÌNH TIẾP NHẬN
- **File:** `prompts/38_LEADS_QUOTES_CONTACT_WORKFLOWS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái.
- **Điều kiện tiên quyết:**
1. P25 form blocks, P33, P36, P37 DONE.
- **Checklist nghiệm thu:**
- [ ] Form blocks lưu lead thật.
- [ ] Không sửa nội dung gốc.
- [ ] Assignment/status history đầy đủ.
- [ ] Spam/rate limiting hoạt động.
- [ ] Notification queue không chặn request.

## P39 — TIN TỨC VÀ KIẾN THỨC
- **File:** `prompts/39_NEWS_CONTENT_MODULE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây CMS bài viết, chuyên mục, tag, author, schedule và public blog SEO-ready.
- **Điều kiện tiên quyết:**
1. P14, P16, P31 DONE.
- **Checklist nghiệm thu:**
- [ ] Draft không public.
- [ ] Scheduled post xuất bản idempotent.
- [ ] Rich text an toàn.
- [ ] Public SSR.

## P40 — GALLERY, ĐỐI TÁC, CHỨNG NHẬN VÀ DỰ ÁN
- **File:** `prompts/40_SHOWCASE_GALLERY_PARTNERS_CERTIFICATIONS_PROJECTS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Xây các module thể hiện năng lực doanh nghiệp và tái sử dụng media.
- **Điều kiện tiên quyết:**
1. P16, P31 DONE.
- **Checklist nghiệm thu:**
- [ ] Không có dữ liệu doanh nghiệp giả.
- [ ] Media relations đúng.
- [ ] Public chỉ published.
- [ ] Blocks/data sources hoàn chỉnh.

## P41 — TÌM KIẾM VÀ KHÁM PHÁ NỘI DUNG PUBLIC
- **File:** `prompts/41_PUBLIC_SEARCH_FILTER_AND_DISCOVERY.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo search nội bộ, filters và related discovery không làm lộ draft hoặc tạo query nguy hiểm.
- **Điều kiện tiên quyết:**
1. P33–P40 core modules DONE.
- **Checklist nghiệm thu:**
- [ ] Search không lộ draft.
- [ ] Tiếng Việt tìm hợp lý theo giải pháp đã chọn.
- [ ] Không raw query injection.
- [ ] Performance có index/explain baseline.

## P42 — SEO METADATA VÀ SOCIAL SHARING
- **File:** `prompts/42_SEO_METADATA_AND_SOCIAL_SHARING.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Quản trị SEO ở page/entity level, canonical, robots, OG và defaults có validation.
- **Điều kiện tiên quyết:**
1. P31–P41 DONE.
- **Checklist nghiệm thu:**
- [ ] Mỗi public response có metadata nhất quán.
- [ ] Preview/admin noindex.
- [ ] Không duplicate title/canonical.
- [ ] SEO fields không nằm lẫn trong arbitrary Page Builder JSON.

## P43 — SITEMAP, STRUCTURED DATA, BREADCRUMB VÀ REDIRECT
- **File:** `prompts/43_SITEMAP_STRUCTURED_DATA_BREADCRUMBS_REDIRECTS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả.
- **Điều kiện tiên quyết:**
1. P42 DONE.
- **Checklist nghiệm thu:**
- [ ] Sitemap valid và không lộ draft.
- [ ] Schema không có dữ liệu giả.
- [ ] Redirect loop bị chặn.
- [ ] Breadcrumb tương ứng UI.
- [ ] Price 0 không xuất hiện schema.

## P44 — ANALYTICS VÀ COOKIE CONSENT
- **File:** `prompts/44_ANALYTICS_COOKIE_CONSENT_AND_TRACKING.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance.
- **Điều kiện tiên quyết:**
1. P13 settings, P42 DONE.
- **Checklist nghiệm thu:**
- [ ] Analytics disabled không tạo request ngoài.
- [ ] Không gửi PII.
- [ ] Consent persistence và revoke hoạt động.
- [ ] Không arbitrary script injection.

## P45 — DASHBOARD, BÁO CÁO VÀ THÔNG BÁO ADMIN
- **File:** `prompts/45_ADMIN_DASHBOARD_REPORTS_AND_NOTIFICATIONS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo dashboard hữu ích cho nội dung và lead, không dựng BI quá mức.
- **Điều kiện tiên quyết:**
1. P38–P44 DONE.
- **Checklist nghiệm thu:**
- [ ] Dashboard có dữ liệu thật.
- [ ] Role chỉ thấy scope cho phép.
- [ ] Không N+1.
- [ ] CSV an toàn.
- [ ] Notification deep link không open redirect.

## P46 — ACCESSIBILITY, RESPONSIVE VÀ PERFORMANCE
- **File:** `prompts/46_ACCESSIBILITY_RESPONSIVE_AND_PERFORMANCE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý.
- **Điều kiện tiên quyết:**
1. Core modules/page builder DONE; FrontEndTemplate có thể đã port hoặc neutral theme được chấp nhận.
- **Checklist nghiệm thu:**
- [ ] Critical accessibility violations được xử lý hoặc documented exception.
- [ ] Public core content usable without JS.
- [ ] Performance budgets có CI gate hợp lý.
- [ ] Không hy sinh fidelity vô cớ.

## P47 — SEEDER VÀ DỮ LIỆU MẪU AN TOÀN
- **File:** `prompts/47_SEEDERS_AND_DEMO_CONTENT.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật.
- **Điều kiện tiên quyết:**
1. P32–P46 DONE.
- **Checklist nghiệm thu:**
- [ ] Production seeder không tạo fake business claims.
- [ ] Demo seeder rõ nhãn.
- [ ] Không duplicate.
- [ ] Page demo validate registry.

## P48 — QA BACKEND TOÀN DIỆN
- **File:** `prompts/48_BACKEND_TESTS_STATIC_ANALYSIS_AND_ARCHITECTURE_QA.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks.
- **Điều kiện tiên quyết:**
1. P47 DONE.
- **Checklist nghiệm thu:**
- [ ] Full suite pass hoặc báo blocker cụ thể, không ghi DONE giả.
- [ ] Prefix/security critical tests pass.
- [ ] No pending migration.
- [ ] Composer audit được xử lý/ghi risk.

## P49 — QA ANGULAR, E2E VÀ VISUAL REGRESSION
- **File:** `prompts/49_ANGULAR_E2E_AND_VISUAL_QA.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages.
- **Điều kiện tiên quyết:**
1. P48 DONE.
2. Admin build integration DONE.
- **Checklist nghiệm thu:**
- [ ] Lint/unit/build pass.
- [ ] Critical E2E pass.
- [ ] Visual diffs được review.
- [ ] No console error trong workflows.
- [ ] Deferred source parity được ghi, không che.

## P50 — BUILD REPRODUCIBLE VÀ CI
- **File:** `prompts/50_BUILD_SCRIPTS_AND_CI_PIPELINES.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile.
- **Điều kiện tiên quyết:**
1. P48–P49 DONE.
- **Checklist nghiệm thu:**
- [ ] CI từ checkout sạch có thể chạy.
- [ ] Không dùng npm install thay npm ci.
- [ ] Không chứa secret.
- [ ] Fail khi prefix/test/build fail.

## P51 — DOCKER VÀ TRIỂN KHAI PRODUCTION
- **File:** `prompts/51_DOCKER_AND_PRODUCTION_DEPLOYMENT.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Chuẩn hóa môi trường Nginx/PHP-FPM/queue/scheduler/MySQL/Redis và quy trình deploy an toàn.
- **Điều kiện tiên quyết:**
1. P50 DONE.
- **Checklist nghiệm thu:**
- [ ] Containers start/healthy ở môi trường test.
- [ ] DB/Redis không public.
- [ ] Admin deep link/public routes hoạt động.
- [ ] Queue/scheduler chạy.
- [ ] No secret in image/history.

## P52 — BACKUP, MONITORING VÀ VẬN HÀNH
- **File:** `prompts/52_BACKUPS_MONITORING_LOGGING_AND_OPERATIONS.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố.
- **Điều kiện tiên quyết:**
1. P51 DONE.
- **Checklist nghiệm thu:**
- [ ] Backup chưa restore-test không được coi là hoàn tất.
- [ ] Health không lộ secret.
- [ ] Alert ownership/escalation documented.
- [ ] Retention rõ.

## P53 — SECURITY REVIEW TOÀN HỆ THỐNG
- **File:** `prompts/53_SECURITY_REVIEW_AND_HARDENING.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production.
- **Điều kiện tiên quyết:**
1. P48–P52 DONE.
- **Checklist nghiệm thu:**
- [ ] Critical/high được fix hoặc production blocked rõ.
- [ ] Regression tests cho finding.
- [ ] No false claim 'secure tuyệt đối'.
- [ ] Threat model cập nhật.

## P54 — NHẬP NỘI DUNG VÀ UAT
- **File:** `prompts/54_CONTENT_MIGRATION_AND_UAT.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Đưa nội dung thật vào staging, kiểm thử nghiệp vụ/visual/SEO với đại diện người dùng.
- **Điều kiện tiên quyết:**
1. P53 DONE.
2. Content/company data được cung cấp; external source gates production-ready hoặc có acceptance.
- **Checklist nghiệm thu:**
- [ ] UAT sign-off hoặc blocker list.
- [ ] No demo/fake data còn trên production dataset.
- [ ] Import dry-run/re-run an toàn.
- [ ] Forms gửi đúng team.
- [ ] Source gates không còn silent deferred.

## P55 — CUTOVER PRODUCTION
- **File:** `prompts/55_PRODUCTION_CUTOVER.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Triển khai production theo checklist có backup, rollback và verification cụ thể.
- **Điều kiện tiên quyết:**
1. P54 UAT approved.
2. P53 no unresolved critical/high.
3. Domain/TLS/production env sẵn sàng.
- **Checklist nghiệm thu:**
- [ ] Production healthy.
- [ ] No debug/secret leakage.
- [ ] DB schema đúng prefix.
- [ ] Admin/public deep links hoạt động.
- [ ] Rollback đã sẵn sàng.

## P56 — TÀI LIỆU CUỐI, BÀN GIAO VÀ KẾ HOẠCH BẢO TRÌ
- **File:** `prompts/56_FINAL_DOCUMENTATION_HANDOVER_AND_MAINTENANCE.md`
- **Cờ:** `REQUIRED`
- **Mục tiêu:** Đóng dự án với tài liệu vận hành, developer onboarding, admin guide, schema/API/page builder/media guide và backlog.
- **Điều kiện tiên quyết:**
1. P55 DONE hoặc release candidate được chủ dự án chấp nhận.
- **Checklist nghiệm thu:**
- [ ] Người mới có thể setup theo docs.
- [ ] Admin guide phản ánh UI thật.
- [ ] Không có secret.
- [ ] Deferred/risk minh bạch.
- [ ] Final test/build links/results được ghi.
