# DANH SÁCH 57 PROMPT — HỒNG VÂN V2

Chạy **đúng thứ tự P00 → P56**. Mỗi prompt là một checkpoint; chỉ sang bước tiếp theo sau khi kiểm tra báo cáo, test/build, `git diff` và `docs/CODEX_STATE.md`.

## Source tham chiếu đã khóa

```text
Template/                          # Angular Admin template
FrontEndTemplate/                  # Website public frontend template
SourceIntegrations/StayHubMedia/   # Media Manager source tham chiếu
```

Prompt P19 dùng file `19_PORT_PUBLIC_FRONTEND_TEMPLATE.md` và đọc source từ `FrontEndTemplate/`.

## Mẫu giao việc cho Codex

```text
Hãy đọc và thực hiện chính xác file:
prompts/NN_TEN_PROMPT.md

Chỉ thực hiện PNN. Không chạy prompt kế tiếp.
Tuân thủ AGENTS.md; chạy test/build được yêu cầu; cập nhật state rồi báo cáo và dừng.
```

## 00 — Governance

P00–P03: kiểm kê, source inventory, ADR và repository baseline

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P00 | THIẾT LẬP BASELINE VÀ KIỂM KÊ REPOSITORY | `REQUIRED` | Xác nhận trạng thái project trước khi sinh framework hoặc sửa source; tạo baseline có thể kiểm chứng để mọi prompt sau không làm việc dựa trên giả định. | `00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md` |
| P01 | KIỂM KÊ TEMPLATE VÀ EXTERNAL SOURCE | `REQUIRED` | Phân tích có mục tiêu các source tham chiếu đang tồn tại, tạo inventory và mapping ban đầu mà không chỉnh source. | `01_EXTERNAL_SOURCE_INVENTORY.md` |
| P02 | CHỐT ADR, MODULE MAP VÀ KẾ HOẠCH BÀN GIAO | `REQUIRED` | Biến blueprint thành tài liệu kiến trúc thực thi được, có dependency map và thứ tự module rõ ràng. | `02_ARCHITECTURE_RECORDS_AND_DELIVERY_PLAN.md` |
| P03 | THIẾT LẬP REPOSITORY HYGIENE VÀ BASELINE CÔNG CỤ | `REQUIRED` | Hoàn thiện cấu hình repository, ignore, scripts placeholder hợp lệ và quy trình làm việc trước khi bootstrap framework. | `03_REPOSITORY_HYGIENE_AND_DEVELOPER_BASELINE.md` |

## 01 — Foundation

P04–P09: Laravel, Angular, template Admin, build, database và API foundation

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P04 | KHỞI TẠO LARAVEL 13 BACKEND | `REQUIRED` | Khởi tạo Laravel 13 sạch trong `BackEnd/`, pin PHP 8.5 và chuẩn bị nền tảng Blade/API mà không phá AGENTS hiện có. | `04_BOOTSTRAP_LARAVEL_13_BACKEND.md` |
| P05 | KHỞI TẠO ANGULAR 22.1 ADMIN | `REQUIRED` | Khởi tạo Angular standalone admin tại `Admin/` đúng phiên bản, strict mode và cấu trúc feature-ready. | `05_BOOTSTRAP_ANGULAR_22_ADMIN.md` |
| P06 | PORT TEMPLATE ADMIN VÀO ANGULAR 22 | `REQUIRED` | Tái sử dụng chính xác cấu trúc giao diện, component và theme settings từ `Template/` vào `Admin/` mà không chạy production trực tiếp từ source tham chiếu. | `06_PORT_ADMIN_TEMPLATE.md` |
| P07 | TÍCH HỢP BUILD ANGULAR VÀO LARAVEL | `REQUIRED` | Thiết lập build/sync reproducible để Angular admin chạy ở `/admin/` và output nằm trong `BackEnd/public/admin/browser/`. | `07_INTEGRATE_ADMIN_BUILD_WITH_LARAVEL.md` |
| P08 | XÂY NỀN DATABASE VÀ CƯỠNG CHẾ TIỀN TỐ | `REQUIRED` | Chuyển mọi bảng framework/core sang tên `hongvan_*`, thiết lập conventions và CI check để không thể tạo bảng sai prefix. | `08_DATABASE_FOUNDATION_AND_PREFIX_ENFORCEMENT.md` |
| P09 | XÂY NỀN API ADMIN V1 | `REQUIRED` | Chuẩn hóa response, pagination, filtering, errors, request IDs và route versioning trước khi thêm module. | `09_ADMIN_API_FOUNDATION.md` |

## 02 — Identity, CMS & Security

P10–P15: authentication, RBAC, user theme, settings, localization và audit/security

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P10 | XÁC THỰC ADMIN BẰNG SANCTUM COOKIE/SESSION | `REQUIRED` | Xây đăng nhập, đăng xuất, current user, password reset và session security cho Angular admin cùng origin. | `10_AUTHENTICATION_SANCTUM_SESSION_CSRF.md` |
| P11 | QUẢN LÝ NGƯỜI DÙNG, VAI TRÒ VÀ QUYỀN | `REQUIRED` | Xây RBAC chi tiết, API và UI quản lý identity theo nguyên tắc deny-by-default. | `11_RBAC_USERS_ROLES_PERMISSIONS.md` |
| P12 | LƯU THEME ADMIN THEO TỪNG USER | `REQUIRED` | Kết nối theme settings đã port từ template với server để mỗi tài khoản có cấu hình riêng và fallback an toàn. | `12_PER_USER_ADMIN_THEME_PREFERENCES.md` |
| P13 | THIẾT LẬP THÔNG TIN CÔNG TY VÀ CẤU HÌNH TOÀN CỤC | `REQUIRED` | Xây Settings quản trị toàn bộ thông tin Công Ty TNHH DV VT Hồng Vân mà không hardcode dữ liệu chưa được cung cấp. | `13_COMPANY_SETTINGS_BRANCHES_CONTACT_CHANNELS.md` |
| P14 | ĐA NGÔN NGỮ VÀ TIMEZONE | `REQUIRED` | Thiết lập tiếng Việt mặc định, tiếng Anh sẵn sàng bật, translation-table conventions và hiển thị giờ Việt Nam. | `14_LOCALIZATION_AND_TIMEZONE_FOUNDATION.md` |
| P15 | NHẬT KÝ HOẠT ĐỘNG VÀ HARDENING NỀN | `REQUIRED` | Xây audit trail, security headers, rate limiting và redaction trước các module nội dung. | `15_AUDIT_LOG_AND_SECURITY_FOUNDATION.md` |

## 03 — Media & Frontend

P16–P20: Media domain, StayHub Media, Blade public, FrontEndTemplate và Theme Studio

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P16 | XÂY DOMAIN MEDIA ĐỘC LẬP UI | `REQUIRED` | Tạo hạ tầng media an toàn, API contract và picker interface để các module dùng ngay, trước khi clone UI StayHub. | `16_MEDIA_DOMAIN_FOUNDATION.md` |
| P17 | CLONE MEDIA MANAGER TỪ STAYHUB | `DEFERRED_ALLOWED` | Clone chính xác chức năng và trải nghiệm trang media tham chiếu vào Hồng Vân, trên domain/API an toàn đã có. | `17_CLONE_STAYHUB_MEDIA_MANAGER.md` |
| P18 | KHỞI TẠO FRONTEND PUBLIC BẰNG LARAVEL BLADE | `REQUIRED` | Tạo shell Blade SSR, asset pipeline, layout và component primitives trung tính để không phụ thuộc FrontEndTemplate chưa có. | `18_BLADE_PUBLIC_FRONTEND_FOUNDATION.md` |
| P19 | PORT FRONTEND TEMPLATE PUBLIC VÀO LARAVEL BLADE | `DEFERRED_ALLOWED` | Port source giao diện trong `FrontEndTemplate/` sang Laravel Blade, tách design tokens và ánh xạ từng section thành block của Page Builder. | `19_PORT_PUBLIC_FRONTEND_TEMPLATE.md` |
| P20 | THEME STUDIO CHO WEBSITE PUBLIC | `REQUIRED` | Cho admin quản lý theme public qua token/version an toàn, tách biệt theme cá nhân của admin. | `20_PUBLIC_THEME_STUDIO.md` |

## 04 — Page Builder

P21–P31: schema, blocks, editor, preview, versioning, global regions và routing public

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P21 | PAGE BUILDER SCHEMA VÀ BLOCK REGISTRY | `REQUIRED` | Xây lõi document, block registry, validation, migrations và API metadata phía server. | `21_PAGE_BUILDER_DOCUMENT_SCHEMA_AND_REGISTRY.md` |
| P22 | BLOCK LAYOUT NỀN | `REQUIRED` | Triển khai các block bố cục có nested constraints và renderer Blade dùng design tokens. | `22_PAGE_BUILDER_LAYOUT_BLOCKS.md` |
| P23 | BLOCK NỘI DUNG VÀ MEDIA | `REQUIRED` | Triển khai block nội dung phổ biến và media, với sanitization và accessibility. | `23_PAGE_BUILDER_CONTENT_AND_MEDIA_BLOCKS.md` |
| P24 | BLOCK DỮ LIỆU NGHIỆP VỤ | `REQUIRED` | Tạo block động kết nối dữ liệu sản phẩm/dịch vụ/vận chuyển/kho/nội dung qua binding registry, không cho query tùy ý. | `24_PAGE_BUILDER_DYNAMIC_BUSINESS_BLOCKS.md` |
| P25 | BLOCK FORM VÀ CTA TẠO LEAD | `REQUIRED` | Tạo block contact/quote/transport/warehouse request bằng form definition an toàn, có anti-spam và accessibility. | `25_PAGE_BUILDER_FORM_BLOCKS.md` |
| P26 | EDITOR KÉO THẢ PAGE BUILDER TRONG ANGULAR | `REQUIRED` | Xây UI editor gồm palette, document tree, canvas host, property inspector, responsive controls và undo/redo. | `26_ANGULAR_PAGE_BUILDER_EDITOR.md` |
| P27 | LIVE PREVIEW BẰNG BLADE IFRAME | `REQUIRED` | Bảo đảm canvas admin render đúng markup/CSS public thông qua preview session ký và Redis. | `27_BLADE_IFRAME_LIVE_PREVIEW.md` |
| P28 | VERSIONING VÀ XUẤT BẢN PAGE | `REQUIRED` | Hoàn thiện draft/autosave, immutable versions, publish, scheduled publish, rollback và cache invalidation. | `28_PAGE_VERSIONING_AUTOSAVE_PUBLISH_SCHEDULE_ROLLBACK.md` |
| P29 | PAGE TEMPLATES, IMPORT/EXPORT VÀ EDIT LOCKS | `REQUIRED` | Tăng khả năng tái sử dụng page mà vẫn giữ schema, version và bảo mật. | `29_PAGE_TEMPLATES_IMPORT_EXPORT_AND_EDIT_LOCKS.md` |
| P30 | MENU, HEADER, FOOTER VÀ GLOBAL REGIONS | `REQUIRED` | Cho admin quản lý navigation và vùng dùng chung, sử dụng cùng block renderer và versioning. | `30_MENUS_HEADER_FOOTER_GLOBAL_REGIONS.md` |
| P31 | ROUTING PUBLIC VÀ CÁC TRANG LÕI | `REQUIRED` | Đưa page đã publish ra URL public, xử lý slug/locale/home/preview/404/410 và trang công ty cơ bản. | `31_PUBLIC_ROUTING_CORE_PAGES_AND_ERROR_PAGES.md` |

## 05 — Business Modules

P32–P40: sản phẩm, cây trồng, dịch vụ, vận chuyển, kho, lead và nội dung

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P32 | DOMAIN SẢN PHẨM PHÂN BÓN | `REQUIRED` | Xây schema/domain sản phẩm, danh mục, thương hiệu, thuộc tính, media và pricing modes đúng website giới thiệu. | `32_PRODUCT_DOMAIN_AND_TAXONOMY.md` |
| P33 | QUẢN TRỊ SẢN PHẨM VÀ CATALOG PUBLIC | `REQUIRED` | Xây CRUD admin, listing/detail public, filter/search và CTA báo giá cho sản phẩm. | `33_PRODUCT_ADMIN_AND_PUBLIC_CATALOG.md` |
| P34 | GIẢI PHÁP THEO CÂY TRỒNG | `REQUIRED` | Xây nội dung cây trồng, giai đoạn, giải pháp dinh dưỡng và liên kết sản phẩm để tăng giá trị chuyên môn/SEO. | `34_CROP_SOLUTIONS_MODULE.md` |
| P35 | MODULE DỊCH VỤ CHUNG | `REQUIRED` | Quản trị dịch vụ công ty ngoài các entity vận chuyển/kho chuyên biệt và render public. | `35_SERVICES_MODULE.md` |
| P36 | VẬN CHUYỂN, ĐỘI XE VÀ TUYẾN | `REQUIRED` | Xây module giới thiệu năng lực vận chuyển và nhận yêu cầu, không biến thành TMS điều phối. | `36_TRANSPORTATION_FLEET_ROUTES_MODULE.md` |
| P37 | KHO BÃI VÀ YÊU CẦU THUÊ KHO | `REQUIRED` | Xây module giới thiệu kho, tiện ích, dịch vụ và nhận nhu cầu thuê kho, không biến thành WMS. | `37_WAREHOUSES_MODULE.md` |
| P38 | LEAD, BÁO GIÁ VÀ QUY TRÌNH TIẾP NHẬN | `REQUIRED` | Hoàn thiện persistence/workflow cho contact, product quote, transport và warehouse request, phân công và lịch sử trạng thái. | `38_LEADS_QUOTES_CONTACT_WORKFLOWS.md` |
| P39 | TIN TỨC VÀ KIẾN THỨC | `REQUIRED` | Xây CMS bài viết, chuyên mục, tag, author, schedule và public blog SEO-ready. | `39_NEWS_CONTENT_MODULE.md` |
| P40 | GALLERY, ĐỐI TÁC, CHỨNG NHẬN VÀ DỰ ÁN | `REQUIRED` | Xây các module thể hiện năng lực doanh nghiệp và tái sử dụng media. | `40_SHOWCASE_GALLERY_PARTNERS_CERTIFICATIONS_PROJECTS.md` |

## 06 — SEO & Experience

P41–P46: search, SEO, schema, analytics, dashboard, accessibility và performance

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P41 | TÌM KIẾM VÀ KHÁM PHÁ NỘI DUNG PUBLIC | `REQUIRED` | Tạo search nội bộ, filters và related discovery không làm lộ draft hoặc tạo query nguy hiểm. | `41_PUBLIC_SEARCH_FILTER_AND_DISCOVERY.md` |
| P42 | SEO METADATA VÀ SOCIAL SHARING | `REQUIRED` | Quản trị SEO ở page/entity level, canonical, robots, OG và defaults có validation. | `42_SEO_METADATA_AND_SOCIAL_SHARING.md` |
| P43 | SITEMAP, STRUCTURED DATA, BREADCRUMB VÀ REDIRECT | `REQUIRED` | Hoàn thiện technical SEO bằng dữ liệu thật và không phát schema/giá giả. | `43_SITEMAP_STRUCTURED_DATA_BREADCRUMBS_REDIRECTS.md` |
| P44 | ANALYTICS VÀ COOKIE CONSENT | `REQUIRED` | Cho phép cấu hình analytics có consent, không hardcode script và không làm giảm SEO/performance. | `44_ANALYTICS_COOKIE_CONSENT_AND_TRACKING.md` |
| P45 | DASHBOARD, BÁO CÁO VÀ THÔNG BÁO ADMIN | `REQUIRED` | Tạo dashboard hữu ích cho nội dung và lead, không dựng BI quá mức. | `45_ADMIN_DASHBOARD_REPORTS_AND_NOTIFICATIONS.md` |
| P46 | ACCESSIBILITY, RESPONSIVE VÀ PERFORMANCE | `REQUIRED` | Đưa public/admin/page builder về baseline WCAG, responsive và Core Web Vitals hợp lý. | `46_ACCESSIBILITY_RESPONSIVE_AND_PERFORMANCE.md` |

## 07 — QA & Delivery

P47–P50: seeders, backend QA, Angular/E2E và CI/build

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P47 | SEEDER VÀ DỮ LIỆU MẪU AN TOÀN | `REQUIRED` | Tạo dữ liệu demo đủ test toàn hệ thống mà không giả thông tin pháp lý/chứng nhận/đối tác thật. | `47_SEEDERS_AND_DEMO_CONTENT.md` |
| P48 | QA BACKEND TOÀN DIỆN | `REQUIRED` | Chạy và bổ sung test backend, static analysis, formatter, migration/prefix/security architecture checks. | `48_BACKEND_TESTS_STATIC_ANALYSIS_AND_ARCHITECTURE_QA.md` |
| P49 | QA ANGULAR, E2E VÀ VISUAL REGRESSION | `REQUIRED` | Kiểm tra toàn bộ admin workflows và visual parity cho template/page builder/media/public critical pages. | `49_ANGULAR_E2E_AND_VISUAL_QA.md` |
| P50 | BUILD REPRODUCIBLE VÀ CI | `REQUIRED` | Tạo pipeline kiểm tra backend/admin/security và artifact build theo lockfile. | `50_BUILD_SCRIPTS_AND_CI_PIPELINES.md` |

## 08 — Operations

P51–P53: Docker/deployment, backup/monitoring và security hardening

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P51 | DOCKER VÀ TRIỂN KHAI PRODUCTION | `REQUIRED` | Chuẩn hóa môi trường Nginx/PHP-FPM/queue/scheduler/MySQL/Redis và quy trình deploy an toàn. | `51_DOCKER_AND_PRODUCTION_DEPLOYMENT.md` |
| P52 | BACKUP, MONITORING VÀ VẬN HÀNH | `REQUIRED` | Thiết lập backup/restore, log rotation, health/metrics và runbook sự cố. | `52_BACKUPS_MONITORING_LOGGING_AND_OPERATIONS.md` |
| P53 | SECURITY REVIEW TOÀN HỆ THỐNG | `REQUIRED` | Thực hiện review bảo mật dựa trên code và attack surface trước UAT/production. | `53_SECURITY_REVIEW_AND_HARDENING.md` |

## 09 — Launch

P54–P56: UAT, production cutover và bàn giao

| # | Prompt | Flag | Mục tiêu | File |
|---:|---|---|---|---|
| P54 | NHẬP NỘI DUNG VÀ UAT | `REQUIRED` | Đưa nội dung thật vào staging, kiểm thử nghiệp vụ/visual/SEO với đại diện người dùng. | `54_CONTENT_MIGRATION_AND_UAT.md` |
| P55 | CUTOVER PRODUCTION | `REQUIRED` | Triển khai production theo checklist có backup, rollback và verification cụ thể. | `55_PRODUCTION_CUTOVER.md` |
| P56 | TÀI LIỆU CUỐI, BÀN GIAO VÀ KẾ HOẠCH BẢO TRÌ | `REQUIRED` | Đóng dự án với tài liệu vận hành, developer onboarding, admin guide, schema/API/page builder/media guide và backlog. | `56_FINAL_DOCUMENTATION_HANDOVER_AND_MAINTENANCE.md` |

## Prompt được phép tạm hoãn

- **P17:** source `SourceIntegrations/StayHubMedia/` chưa có.
- **P19:** source `FrontEndTemplate/` chưa có.

`DEFERRED` không phải hoàn tất. Hai gate phải được xử lý trước UAT/production hoặc có acceptance chính thức về phạm vi thay thế.
