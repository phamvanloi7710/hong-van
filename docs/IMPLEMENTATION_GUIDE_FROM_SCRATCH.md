# HƯỚNG DẪN TRIỂN KHAI HỆ THỐNG HỒNG VÂN TỪ ĐẦU ĐẾN PRODUCTION


> **Đường dẫn frontend template bắt buộc:** `FrontEndTemplate/`. Source có thể nằm trực tiếp trong thư mục này hoặc trong một thư mục con để giữ nguyên bundle; P01/P19 phải tìm kiếm đệ quy và không sửa source tham chiếu.
**Bộ prompt:** V2  
**Ngày cập nhật:** 02/08/2026  
**Doanh nghiệp:** Công Ty TNHH DV VT Hồng Vân

> Quy ước mới: template website public luôn đặt tại `FrontEndTemplate/`. Không dùng các đường dẫn frontend template của bộ V1 trong bộ V2.

---

## 1. Mục tiêu và kiến trúc cố định

Hệ thống gồm:

```text
BackEnd/                   Laravel 13 + Blade SSR + REST API
Admin/                     Angular 22.1 cho trang quản trị
Template/                  Source template Angular admin, read-only
FrontEndTemplate/          Source template website public, read-only
SourceIntegrations/
└── StayHubMedia/           Source tham chiếu Media Manager, read-only
```

Nguyên tắc không được thay đổi trong lúc triển khai:

- Website public render bằng Laravel Blade để bảo đảm SEO cốt lõi.
- Angular chỉ dùng cho admin.
- Admin chạy dưới `/admin`; API quản trị dưới `/api/admin/v1`.
- Page Builder lưu JSON có schema, không lưu/chạy PHP, Blade hoặc JavaScript tùy ý từ database.
- Preview Page Builder phải dùng chính Blade renderer và CSS của frontend public.
- Mọi bảng do hệ thống tạo phải bắt đầu bằng `hongvan_`.
- Sản phẩm là catalog giới thiệu và nhận báo giá; không tự thêm cart, checkout hoặc thanh toán.
- Giá rỗng/0/không hợp lệ không được hiển thị `0đ`; phải chuyển sang nội dung liên hệ báo giá.

---

## 2. Chuẩn bị trước khi bắt đầu

### 2.1. Công cụ local

Trên máy Windows, kiểm tra:

```powershell
php -v
composer --version
node --version
npm --version
git --version
mysql --version
docker --version
```

Baseline của bộ prompt:

- Laravel 13.x.
- PHP 8.5.x cho dự án này; Laravel 13 có mức tối thiểu thấp hơn nhưng project chủ động khóa 8.5.x.
- Angular 22.1.x.
- Node.js 24.x tương thích Angular 22; prompt P05 phải xác nhận patch thực tế trước khi scaffold.
- MySQL 8.4 LTS.
- Redis cho cache, queue, rate limit và preview session.

Không tự chạy `composer create-project`, `ng new`, `npm update`, `composer update` hoặc migration trước P04/P05/P08. P00–P03 dùng để kiểm kê và khóa baseline.

### 2.2. Những dữ liệu cần chuẩn bị dần

Không cần có hết ở P00, nhưng phải có trước P13/P54:

- Tên pháp lý chính xác, mã số thuế, địa chỉ trụ sở và chi nhánh.
- Hotline, email, Zalo, mạng xã hội, giờ làm việc.
- Logo, favicon, bộ màu, font và tài liệu nhận diện.
- Danh mục/sản phẩm phân bón, quy cách, thành phần, công dụng, hướng dẫn, hình ảnh và chính sách hiển thị giá.
- Thông tin dịch vụ vận chuyển, đội xe, tuyến, năng lực thực tế.
- Thông tin kho bãi, diện tích/sức chứa/tiện ích thực tế.
- Chứng nhận, đối tác, dự án và hồ sơ năng lực có quyền sử dụng.
- Chính sách bảo mật, điều khoản, cookie và người chịu trách nhiệm nội dung.

Không để Codex tự bịa dữ liệu pháp lý, chứng nhận, đối tác hoặc con số năng lực.

---

## 3. Tạo project local đúng cấu trúc

### 3.1. Giải nén

Giải nén file `HongVan_Full_Prompt_Kit_v2.zip` vào một thư mục mới, ví dụ:

```text
D:\www\HongVan
```

Không giải nén đè lên project đã có code nếu chưa sao lưu.

Sau khi giải nén, các file sau phải nằm ngay ở root:

```text
D:\www\HongVan\AGENTS.md
D:\www\HongVan\START_HERE.md
D:\www\HongVan\prompts\00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md
D:\www\HongVan\docs\CODEX_STATE.md
```

Kiểm tra bằng PowerShell:

```powershell
cd D:\www\HongVan

Test-Path .\AGENTS.md
Test-Path .\START_HERE.md
Test-Path .\prompts\00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md
Test-Path .\prompts\56_FINAL_DOCUMENTATION_HANDOVER_AND_MAINTENANCE.md
Test-Path .\docs\CODEX_STATE.md
```

Tất cả phải trả về `True`.

### 3.2. Đặt template admin

Chép source template Angular admin vào:

```text
D:\www\HongVan\Template\
```

Nên có:

```text
Template/
├── AGENTS.md
├── package.json
├── angular.json
├── src/
└── ...
```

Không chép template admin vào `Admin/`. `Template/` là source tham chiếu; `Admin/` là ứng dụng thật do P05/P06 xây dựng.

Không cần chép `node_modules`, `dist`, `.angular/cache`, `.git` hoặc build output. Giữ `package.json`, lockfile, source, asset và tài liệu license.

### 3.3. Đặt template website public

Chép source template website public vào đúng thư mục mới:

```text
D:\www\HongVan\FrontEndTemplate\
```

Ví dụ:

```text
FrontEndTemplate/
├── AGENTS.md
├── package.json            nếu template có build tool
├── index.html              nếu là HTML template
├── assets/
├── css/
├── js/
└── ...
```

Không chép vào `BackEnd/resources/views/`; P19 sẽ audit và port sang Blade. Không sửa source gốc trong `FrontEndTemplate/`.

Nếu chưa có source ở thời điểm bắt đầu, giữ placeholder. P01/P19 phải ghi `DEFERRED — SOURCE MISSING`; không được tự nghĩ ra giao diện production cuối.

### 3.4. Đặt source Media StayHub

Khi có source tham chiếu, chép vào:

```text
D:\www\HongVan\SourceIntegrations\StayHubMedia\
```

Không chép dữ liệu production, secret, token, `.env`, upload thật hoặc thông tin khách hàng. Chỉ cung cấp phần source cần để audit chức năng/giao diện.

### 3.5. Kiểm tra source gate

```powershell
cd D:\www\HongVan

Get-ChildItem .\Template -Filter package.json -Recurse
Get-ChildItem .\Template -Filter angular.json -Recurse
Get-ChildItem .\FrontEndTemplate -Force
Get-ChildItem .\SourceIntegrations\StayHubMedia -Force
```

Tốt nhất hãy đặt cả template admin và template public trước P00 để P01 lập inventory ngay từ đầu. Media StayHub có thể bổ sung sau.

---

## 4. Khởi tạo Git và checkpoint ban đầu

```powershell
cd D:\www\HongVan

git init
git branch -M main
git add .
git status
git commit -m "chore: initialize Hong Van prompt kit v2"
```

Nếu Git yêu cầu danh tính, cấu hình riêng project:

```powershell
git config user.name "Loi Pham"
git config user.email "email-cua-anh@example.com"
```

Template reference được `.gitignore` bỏ qua mặc định vì có thể chứa tài sản có giấy phép. Chỉ bỏ ignore khi repository private và license cho phép.

Nếu dùng remote private:

```powershell
git remote add origin <PRIVATE_GIT_REMOTE>
git push -u origin main
```

Không đưa secret, `.env`, database dump, upload thật hoặc credential vào Git.

---

## 5. Mở project trong Codex

Mở đúng root:

```text
D:\www\HongVan
```

Codex phải nhìn thấy `AGENTS.md`, `prompts/`, `docs/`, `BackEnd/`, `Admin/`, `Template/`, `FrontEndTemplate/`.

Nên dùng một thread mới cho mỗi prompt hoặc mỗi checkpoint lớn để context không phình quá mức. Trạng thái bền vững nằm trong Git, `docs/CODEX_STATE.md`, `docs/TASK_LEDGER.md` và các report, không phụ thuộc vào việc giữ một cuộc chat quá dài.

### Mẫu giao việc

```text
Hãy mở và thực hiện đúng toàn bộ nội dung file:

prompts/00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md

Yêu cầu bắt buộc:
- Chỉ thực hiện P00.
- Đọc AGENTS.md tại root và các tài liệu P00 yêu cầu.
- Không cài Laravel hoặc Angular.
- Không sửa Template, FrontEndTemplate hoặc SourceIntegrations.
- Không chạy P01.
- Chạy kiểm tra tối thiểu, cập nhật CODEX_STATE/TASK_LEDGER và tạo báo cáo cuối.
- Dừng lại sau khi báo cáo.
```

Cho các prompt khác, dùng `prompts/RUN_PROMPT_TEMPLATE.md`.

---

## 6. Quy trình bắt buộc sau mỗi prompt

Sau khi Codex báo hoàn tất:

```powershell
cd D:\www\HongVan

git status --short --branch
git diff --check
git diff --stat
Get-Content .\docs\CODEX_STATE.md
Get-Content .\docs\TASK_LEDGER.md
```

Kiểm tra lần lượt:

1. `status` phải là `DONE`, hoặc `DEFERRED` đúng điều kiện P17/P19.
2. `last_completed_prompt` đúng số vừa chạy.
3. `next_prompt` đúng bước kế tiếp.
4. File thay đổi đúng scope.
5. Source read-only không có diff.
6. Test/lint/build được chạy thật và có kết quả.
7. Không có lỗi bị che bằng cách xóa test, tắt rule hoặc bỏ validation.
8. Không có secret hoặc dữ liệu thật trong diff.

Nếu đạt:

```powershell
git add -A
git commit -m "feat(pXX): mô tả ngắn phần đã hoàn thành"
git push origin main
```

Nếu `PARTIAL` hoặc `BLOCKED`, không chạy prompt kế tiếp. Yêu cầu Codex sửa đúng blocker trong cùng scope rồi kiểm tra lại.

Không dùng `git reset --hard` khi chưa xác nhận vì có thể xóa thay đổi của chính anh.

---

## 7. Hướng dẫn từng giai đoạn và từng prompt

### 00 — Governance

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P00 | THIẾT LẬP BASELINE VÀ KIỂM KÊ REPOSITORY | Baseline môi trường, source gate và `docs/reports/P00_BASELINE.md`. |
| P01 | KIỂM KÊ TEMPLATE VÀ EXTERNAL SOURCE | Inventory thật của Template, FrontEndTemplate và source Media; không sửa source tham chiếu. |
| P02 | CHỐT ADR, MODULE MAP VÀ KẾ HOẠCH BÀN GIAO | ADR, module map, delivery phases và Definition of Done được chốt. |
| P03 | THIẾT LẬP REPOSITORY HYGIENE VÀ BASELINE CÔNG CỤ | Git workflow, script kiểm tra prerequisite và bảo vệ source read-only. |

### 01 — Foundation

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P04 | KHỞI TẠO LARAVEL 13 BACKEND | Laravel 13 được bootstrap an toàn trong `BackEnd/`, có smoke test. |
| P05 | KHỞI TẠO ANGULAR 22.1 ADMIN | Angular 22.1 được bootstrap trong `Admin/`, strict mode và test/build nền. |
| P06 | PORT TEMPLATE ADMIN VÀO ANGULAR 22 | Layout, menu, auth screen, theme engine và asset của template admin được port đúng. |
| P07 | TÍCH HỢP BUILD ANGULAR VÀO LARAVEL | Admin build được phục vụ tại `/admin` và output đồng bộ vào `BackEnd/public/admin/browser/`. |
| P08 | XÂY NỀN DATABASE VÀ CƯỠNG CHẾ TIỀN TỐ | Database foundation, migration framework và kiểm tra cưỡng chế prefix `hongvan_`. |
| P09 | XÂY NỀN API ADMIN V1 | API admin v1 có envelope, validation, pagination, error handling và health checks. |

### 02 — Identity, Core CMS & Security

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P10 | XÁC THỰC ADMIN BẰNG SANCTUM COOKIE/SESSION | Đăng nhập/đăng xuất/session/CSRF bằng Sanctum cùng origin hoạt động an toàn. |
| P11 | QUẢN LÝ NGƯỜI DÙNG, VAI TRÒ VÀ QUYỀN | User, role, permission, policy và permission matrix hoàn chỉnh. |
| P12 | LƯU THEME ADMIN THEO TỪNG USER | Theme admin được lưu riêng theo từng user và khôi phục khi đăng nhập. |
| P13 | THIẾT LẬP THÔNG TIN CÔNG TY VÀ CẤU HÌNH TOÀN CỤC | Thông tin công ty, chi nhánh, kênh liên hệ và setting toàn cục có quản trị. |
| P14 | ĐA NGÔN NGỮ VÀ TIMEZONE | Nền đa ngôn ngữ, locale, timezone và quy ước lưu UTC. |
| P15 | NHẬT KÝ HOẠT ĐỘNG VÀ HARDENING NỀN | Audit log, rate limit và các hardening nền tảng được bật. |

### 03 — Media & Frontend

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P16 | XÂY DOMAIN MEDIA ĐỘC LẬP UI | Media domain, storage abstraction, upload validation, usage tracking và API nền. |
| P17 | CLONE MEDIA MANAGER TỪ STAYHUB | Media Manager đạt parity với source StayHub hoặc được đánh dấu DEFERRED đúng sự thật. |
| P18 | KHỞI TẠO FRONTEND PUBLIC BẰNG LARAVEL BLADE | Blade public skeleton SSR, layout, asset pipeline và trang cơ sở hoạt động. |
| P19 | PORT FRONTEND TEMPLATE PUBLIC VÀO LARAVEL BLADE | Template trong `FrontEndTemplate/` được port sang Blade với visual fidelity và design tokens. |
| P20 | THEME STUDIO CHO WEBSITE PUBLIC | Theme Studio public quản lý token an toàn, có preview và fallback. |

### 04 — Page Builder

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P21 | PAGE BUILDER SCHEMA VÀ BLOCK REGISTRY | Page document schema, block registry, validator và migration schema được xây. |
| P22 | BLOCK LAYOUT NỀN | Các block layout nền có Blade renderer và responsive contract. |
| P23 | BLOCK NỘI DUNG VÀ MEDIA | Block nội dung/media có sanitize, alt text và media picker integration. |
| P24 | BLOCK DỮ LIỆU NGHIỆP VỤ | Block động cho sản phẩm, dịch vụ, kho, vận chuyển, bài viết và đối tác. |
| P25 | BLOCK FORM VÀ CTA TẠO LEAD | Block form/CTA tạo lead có validation, chống spam và tracking nguồn. |
| P26 | EDITOR KÉO THẢ PAGE BUILDER TRONG ANGULAR | Angular Page Builder editor có palette, canvas, layer tree và property panel. |
| P27 | LIVE PREVIEW BẰNG BLADE IFRAME | Live preview dùng chính Blade renderer/CSS public trong iframe bảo mật. |
| P28 | VERSIONING VÀ XUẤT BẢN PAGE | Autosave, undo/redo, draft, publish, schedule, revision và rollback. |
| P29 | PAGE TEMPLATES, IMPORT/EXPORT VÀ EDIT LOCKS | Page template, import/export JSON an toàn và edit lock. |
| P30 | MENU, HEADER, FOOTER VÀ GLOBAL REGIONS | Menu builder, header, footer và global/reusable regions. |
| P31 | ROUTING PUBLIC VÀ CÁC TRANG LÕI | Routing public, trang lõi, 404/500 và fallback published page. |

### 05 — Business Modules

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P32 | DOMAIN SẢN PHẨM PHÂN BÓN | Domain sản phẩm, taxonomy, quy tắc giá và migration/model có prefix. |
| P33 | QUẢN TRỊ SẢN PHẨM VÀ CATALOG PUBLIC | CRUD admin sản phẩm và catalog public SSR có bộ lọc/chi tiết/báo giá. |
| P34 | GIẢI PHÁP THEO CÂY TRỒNG | Giải pháp theo cây trồng, giai đoạn và liên kết sản phẩm. |
| P35 | MODULE DỊCH VỤ CHUNG | Module dịch vụ chung cho nội dung và landing page. |
| P36 | VẬN CHUYỂN, ĐỘI XE VÀ TUYẾN | Đội xe, tuyến vận chuyển và nội dung năng lực vận tải. |
| P37 | KHO BÃI VÀ YÊU CẦU THUÊ KHO | Kho bãi, tiện ích kho và form yêu cầu thuê kho. |
| P38 | LEAD, BÁO GIÁ VÀ QUY TRÌNH TIẾP NHẬN | Lead/contact/quote workflow, phân công, trạng thái, ghi chú và thông báo. |
| P39 | TIN TỨC VÀ KIẾN THỨC | Tin tức, chuyên mục, tag, bài viết và trang public SSR. |
| P40 | GALLERY, ĐỐI TÁC, CHỨNG NHẬN VÀ DỰ ÁN | Gallery, đối tác, chứng nhận, dự án và hồ sơ năng lực. |

### 06 — SEO, Experience & Operations UX

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P41 | TÌM KIẾM VÀ KHÁM PHÁ NỘI DUNG PUBLIC | Tìm kiếm public, filter, discovery và trang kết quả có kiểm soát. |
| P42 | SEO METADATA VÀ SOCIAL SHARING | SEO metadata, canonical, robots, Open Graph và social sharing. |
| P43 | SITEMAP, STRUCTURED DATA, BREADCRUMB VÀ REDIRECT | Sitemap, structured data, breadcrumb và redirect 301. |
| P44 | ANALYTICS VÀ COOKIE CONSENT | Analytics, consent, tracking event và chính sách cookie. |
| P45 | DASHBOARD, BÁO CÁO VÀ THÔNG BÁO ADMIN | Dashboard, báo cáo, notification và KPI vận hành admin. |
| P46 | ACCESSIBILITY, RESPONSIVE VÀ PERFORMANCE | Accessibility, responsive, Core Web Vitals và tối ưu hiệu năng. |

### 07 — QA & Delivery

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P47 | SEEDER VÀ DỮ LIỆU MẪU AN TOÀN | Seeder/demo content an toàn, không bịa dữ liệu pháp lý hoặc chứng nhận. |
| P48 | QA BACKEND TOÀN DIỆN | Backend tests, static analysis, architecture QA và migration rollback QA. |
| P49 | QA ANGULAR, E2E VÀ VISUAL REGRESSION | Angular unit/E2E/visual regression và kiểm thử responsive. |
| P50 | BUILD REPRODUCIBLE VÀ CI | Build reproducible, CI pipelines và quality gates. |

### 08 — Operations & Security

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P51 | DOCKER VÀ TRIỂN KHAI PRODUCTION | Docker/Nginx/PHP-FPM/queue/scheduler và runbook triển khai production. |
| P52 | BACKUP, MONITORING VÀ VẬN HÀNH | Backup, restore drill, monitoring, logging, alert và vận hành định kỳ. |
| P53 | SECURITY REVIEW TOÀN HỆ THỐNG | Security review toàn hệ thống, dependency audit và hardening release. |

### 09 — Launch

| Prompt | Việc chính | Kết quả cần nhìn thấy trước khi đi tiếp |
|---|---|---|
| P54 | NHẬP NỘI DUNG VÀ UAT | Nạp nội dung thật, chạy UAT, xử lý lỗi và ký acceptance. |
| P55 | CUTOVER PRODUCTION | Cutover production có backup, migration, smoke test và rollback plan. |
| P56 | TÀI LIỆU CUỐI, BÀN GIAO VÀ KẾ HOẠCH BẢO TRÌ | Tài liệu bàn giao, tài khoản/quyền sở hữu, bảo trì và roadmap sau release. |


---

## 8. Chi tiết checkpoint P00–P03: Governance

### P00 — Baseline

Mục tiêu là kiểm kê, không code framework. Sau P00 phải có:

```text
docs/reports/P00_BASELINE.md
docs/CODEX_STATE.md
docs/TASK_LEDGER.md
```

Ba gate phải rõ:

```yaml
admin_template_gate: READY | MISSING | INVALID
frontend_template_gate: READY | MISSING | INVALID
stayhub_media_gate: READY | MISSING | INVALID
```

### P01 — Inventory source

P01 đọc manifest/source thật và tạo inventory. Kiểm tra đặc biệt:

- Angular version, layout, menu, theme service, login, assets và license của `Template/`.
- Layout, section, CSS, JS, font, breakpoint, plugin và license của `FrontEndTemplate/`.
- Route/component/API/model/permission của source StayHub nếu có.
- Source thiếu phải ghi `DEFERRED — SOURCE MISSING`, không mô tả chức năng tưởng tượng.

### P02 — Kiến trúc

Phải có ADR cho monorepo, Blade public, Angular admin, Sanctum same-origin, explicit `hongvan_`, Blade iframe preview, source read-only và no e-commerce.

### P03 — Baseline công cụ

P03 tạo script kiểm tra prerequisite và hash source read-only. Chạy script PowerShell được sinh ra và chỉ đi tiếp khi môi trường đáp ứng P04/P05 hoặc blocker đã được xử lý.

---

## 9. Chi tiết checkpoint P04–P09: Framework và database

### Trước P04

- PHP/Composer phải hoạt động.
- Không tự xóa `BackEnd/AGENTS.md` hoặc `.ai/guidelines/`.
- P04 phải bootstrap trong thư mục tạm rồi merge an toàn vì `BackEnd/` không rỗng.

### Trước P05

- Node/npm phù hợp Angular 22.
- Không scaffold đè lên `Admin/AGENTS.md`.
- Angular core và CLI phải cùng minor; lockfile phải được commit.

### P06

Template admin là nguồn chuẩn cho layout/theme. Codex phải port, không dựng một giao diện “gần giống”. Theme engine có sẵn phải được giữ để P12 lưu theo từng user.

### P07

Checkpoint quan trọng:

```text
Admin source: Admin/
Admin production output: BackEnd/public/admin/browser/
Admin URL: /admin
```

Không chỉnh thủ công file build output.

### Trước P08 — tạo database local

Ví dụ MySQL local:

```sql
CREATE DATABASE hongvan_platform
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Cấu hình `BackEnd/.env` local, ví dụ:

```dotenv
APP_NAME=HongVan
APP_ENV=local
APP_URL=http://hongvan.local
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hongvan_platform
DB_USERNAME=root
DB_PASSWORD=
```

Đây chỉ là local. Production phải dùng user/password riêng và secret manager/environment; không commit `.env`.

P08 phải kiểm tra mọi bảng, pivot, model `$table`, foreign key, index và bảng framework đều có prefix `hongvan_`. Không cấu hình prefix toàn connection nếu migration đã ghi tên đầy đủ.

---

## 10. P10–P15: Auth, RBAC, theme và setting

Không bắt đầu Page Builder trước khi nhóm này ổn định.

Checkpoint chức năng:

- Đăng nhập/đăng xuất/CSRF/session hoạt động qua same-origin.
- Backend policy/middleware là nguồn phân quyền thật; việc ẩn nút Angular không đủ.
- User A đổi theme không ảnh hưởng user B.
- Thông tin công ty lấy từ setting, không hardcode rải rác.
- Locale/timezone thống nhất; database lưu UTC.
- Audit log không ghi password, token, cookie hoặc secret.

Trước P13, cung cấp dữ liệu công ty thật hoặc cho phép dùng placeholder rõ ràng. Không dùng con số năng lực giả để “làm đẹp demo”.

---

## 11. P16–P20: Media và frontend Blade

### P17 khi chưa có source StayHub

Cho phép `DEFERRED`. Sau này khi bổ sung source:

1. Chép source vào `SourceIntegrations/StayHubMedia/`.
2. Kiểm tra không có secret/data production.
3. Chạy lại P17.
4. Kiểm tra feature parity matrix và visual/E2E.
5. Cập nhật gate thành `READY/DONE` trước UAT.

### P19 với `FrontEndTemplate/`

Nếu template public đã có, P19 phải:

- Audit source thật.
- Port layout và section sang Blade.
- Tách design tokens.
- Chuyển asset sang Vite/Media/settings helper.
- Loại demo link/data hardcode.
- So sánh desktop/tablet/mobile.
- Không chạy trực tiếp template tĩnh trong production.

Nếu template được bổ sung sau khi P01 đã chạy, hãy cập nhật inventory/source mapping trước hoặc trong P19, rồi ghi rõ trong report. Không giữ trạng thái gate cũ là MISSING.

---

## 12. P21–P31: Page Builder

Đây là phần rủi ro cao nhất, nên kiểm tra từng prompt chặt chẽ.

Kiến trúc đúng:

```text
Angular editor
    -> PageDocument JSON có schema
    -> Preview session/token
    -> Laravel Blade renderer trong iframe
    -> Cùng CSS/font/assets với public website
```

Các lỗi phải từ chối nghiệm thu:

- Angular tự dựng một bản HTML/CSS khác frontend public.
- Cho nhập/chạy JavaScript, PHP hoặc Blade từ database.
- Rich text không sanitize server-side.
- Preview URL không ký số/không kiểm tra quyền.
- Published page render draft.
- Block schema đổi nhưng không có migration/version strategy.
- Không có undo/redo, revision, rollback hoặc edit lock theo prompt.

Sau P31, phải tạo được ít nhất một page từ block, preview đúng style, publish và truy cập bằng route public.

---

## 13. P32–P40: Module nghiệp vụ

Thứ tự không được đảo vì các block động và lead workflow phụ thuộc domain/model/API trước đó.

### Quy tắc sản phẩm

Các mode giá tối thiểu:

```text
fixed
from
range
market
quantity
dealer
contact
hidden
```

Giá null/0/không hợp lệ phải rơi về `contact` hoặc trạng thái hiển thị được cấu hình; không render `0đ`.

### Dịch vụ vận chuyển và kho bãi

Đây là module nội dung/năng lực/lead cho website doanh nghiệp, không tự mở rộng thành TMS/WMS đầy đủ khi chưa có yêu cầu mới.

### Lead

Nội dung gốc khách gửi phải được bảo toàn. Ghi chú nội bộ, assignee và status tách riêng. Form public cần rate limit, validation, chống spam và audit phù hợp.

---

## 14. P41–P46: SEO, tìm kiếm và trải nghiệm

Website public phải SSR cho nội dung SEO cốt lõi. Kiểm tra:

- Title, meta description, canonical, robots, Open Graph.
- Sitemap theo nhóm nội dung.
- Redirect 301 khi đổi slug.
- Structured data đúng dữ liệu thật; không tạo Offer/giá giả.
- Breadcrumb và heading hợp lý.
- Ảnh có alt, width/height và lazy loading phù hợp.
- Consent được áp dụng trước tracking không thiết yếu.
- Mobile/tablet/desktop và keyboard accessibility.
- Performance budget và không tải toàn bộ Angular admin ở public site.

---

## 15. P47–P53: QA, CI, Docker và Security

Không lên production chỉ vì “chạy được trên máy”. Trước P54 phải có:

- Backend test pass.
- Static analysis pass hoặc có waiver được ghi rõ.
- Angular lint/test/build pass.
- E2E và visual regression màn hình chính pass.
- Migration up/down được kiểm tra ở môi trường test.
- CI build từ clean checkout thành công.
- Docker/runbook có health check, queue worker, scheduler, Nginx/PHP-FPM.
- Backup và restore drill được thử.
- Dependency/security audit được xử lý.
- Upload, Page Builder, auth, permission và form public được security review.

Không tắt security rule hoặc bỏ test chỉ để pipeline xanh.

---

## 16. P54–P56: UAT, cutover và bàn giao

### Trước P54

Chuẩn bị:

- Dữ liệu công ty thật đã duyệt.
- Nội dung/sản phẩm/dịch vụ/hình ảnh có quyền sử dụng.
- FrontEndTemplate và Media source gate đã hoàn tất hoặc có acceptance thay thế.
- Danh sách user UAT theo vai trò.
- Kịch bản kiểm thử desktop/mobile.
- Tiêu chí chấp nhận và người ký.

### Trước P55

Không cutover nếu chưa có:

- Domain/DNS/SSL.
- Production `.env` và secret riêng.
- Database/user production riêng.
- Mail sender và queue đã thử.
- Storage/backup target.
- Monitoring/alert.
- Backup ngay trước migration.
- Maintenance window.
- Rollback plan có lệnh và người chịu trách nhiệm.

P55 phải ghi chính xác:

- Commit/tag được triển khai.
- Migration đã chạy.
- Health/smoke test.
- Queue/scheduler.
- URL public/admin.
- Backup ID/thời điểm.
- Kết quả cutover và rollback decision.

### P56

Bàn giao phải có:

- Kiến trúc và runbook.
- Cách local/dev/staging/production.
- Cách build/deploy/rollback.
- Backup/restore/monitoring.
- Danh sách tài khoản và quyền sở hữu được chuyển giao an toàn; không ghi password vào tài liệu Git.
- Quy trình thêm block Page Builder/module mới.
- Lịch bảo trì dependency và security.
- Known issues và roadmap.

---

## 17. Lệnh kiểm tra thường dùng

### Backend

```powershell
cd D:\www\HongVan\BackEnd
composer validate
php artisan about
php artisan test
vendor\bin\pint --test
vendor\bin\phpstan analyse
```

Chỉ chạy lệnh đã tồn tại sau các prompt bootstrap; tên script có thể được P04/P48 điều chỉnh.

### Angular admin

```powershell
cd D:\www\HongVan\Admin
npm ci
npm run lint
npm test -- --watch=false
npm run build
npx playwright test
```

Không chạy `npm install` trong `Template/` hoặc `FrontEndTemplate/` nếu mục tiêu chỉ là inventory. Source tham chiếu là read-only.

### Git

```powershell
git status --short --branch
git diff --check
git diff --stat
git log -5 --oneline
```

### Kiểm tra không sửa source tham chiếu

```powershell
git diff -- Template FrontEndTemplate SourceIntegrations
```

Do các source này bị ignore, script hash từ P03 là lớp kiểm tra bổ sung quan trọng.

---

## 18. Quy tắc xử lý lỗi và rollback khi làm với Codex

Nếu một prompt làm sai scope:

1. Dừng, không chạy prompt kế tiếp.
2. Chạy `git status` và `git diff`.
3. Xác định chính xác file Codex vừa sửa sai.
4. Yêu cầu Codex hoàn nguyên **chỉ** thay đổi ngoài scope do nó tạo.
5. Không dùng `git reset --hard` nếu có thay đổi của người dùng.
6. Chạy lại test/checkpoint.
7. Commit chỉ khi diff sạch.

Nếu migration lỗi local:

- Không sửa migration đã chạy ở production.
- Ở local/test, xử lý theo prompt và trạng thái dự án.
- Ở staging/production, tạo migration sửa mới và có backup/rollback.

Nếu P17/P19 bị `DEFERRED`:

- Ghi rõ blocker/path source cần bổ sung.
- Tiếp tục prompt độc lập nếu architecture cho phép.
- Tạo checkpoint bắt buộc quay lại trước P54/P55.

---

## 19. Checklist hoàn tất toàn dự án

- [ ] P00–P56 có trạng thái và report hợp lệ.
- [ ] P17 và P19 không còn deferred, hoặc có acceptance chính thức.
- [ ] Mọi bảng mang prefix `hongvan_`.
- [ ] Admin Angular giữ đúng template và theme theo user.
- [ ] Frontend Blade giữ đúng template trong `FrontEndTemplate/`.
- [ ] Page Builder preview/public dùng một Blade renderer.
- [ ] Media Manager đạt parity đã thống nhất.
- [ ] Sản phẩm không hiển thị `0đ` khi thiếu giá.
- [ ] Không có cart/checkout/payment ngoài phạm vi.
- [ ] Auth/RBAC/CSRF/rate limit/upload security pass.
- [ ] SEO, sitemap, structured data và redirect pass.
- [ ] Backend/Angular/E2E/visual/CI pass.
- [ ] Backup restore drill pass.
- [ ] UAT được ký.
- [ ] Production cutover có backup và rollback plan.
- [ ] Tài liệu bàn giao và quyền sở hữu hoàn tất.

---

## 20. Bước đầu tiên phải chạy

Sau khi giải nén, đặt source và commit baseline:

```text
prompts/00_PROJECT_BASELINE_AND_REPOSITORY_AUDIT.md
```

Không chạy P01 trước khi P00 hoàn tất và `docs/CODEX_STATE.md` ghi đúng trạng thái.
