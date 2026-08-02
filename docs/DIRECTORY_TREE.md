# CÂY THƯ MỤC ĐÍCH

```text
HongVan/
├── AGENTS.md
├── README.md
├── START_HERE.md
├── HUONG_DAN_TRIEN_KHAI_TU_DAU.md
├── DANH_SACH_PROMPT_CHI_TIET.md
├── CHANGELOG_V2.md
├── MANIFEST.md
├── .editorconfig
├── .gitattributes
├── .gitignore
├── BackEnd/
│   ├── AGENTS.md
│   ├── .ai/
│   │   └── guidelines/
│   │       ├── architecture.md
│   │       ├── database.md
│   │       ├── security.md
│   │       └── testing.md
│   ├── app/
│   │   ├── AGENTS.md
│   │   ├── Domain/
│   │   │   ├── Identity/
│   │   │   │   ├── Actions/
│   │   │   │   ├── Data/
│   │   │   │   ├── Models/
│   │   │   │   ├── Policies/
│   │   │   │   └── Services/
│   │   │   ├── Settings/
│   │   │   ├── Media/
│   │   │   ├── PageBuilder/
│   │   │   │   ├── Actions/
│   │   │   │   ├── Blocks/
│   │   │   │   ├── Contracts/
│   │   │   │   ├── Data/
│   │   │   │   ├── Models/
│   │   │   │   ├── Registry/
│   │   │   │   ├── Rendering/
│   │   │   │   ├── Schemas/
│   │   │   │   └── Validation/
│   │   │   ├── Products/
│   │   │   ├── CropSolutions/
│   │   │   ├── Services/
│   │   │   ├── Transportation/
│   │   │   ├── Warehouses/
│   │   │   ├── Leads/
│   │   │   ├── Content/
│   │   │   ├── Showcase/
│   │   │   ├── Seo/
│   │   │   ├── Analytics/
│   │   │   ├── Audit/
│   │   │   └── Shared/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/Api/V1/
│   │   │   │   ├── Web/
│   │   │   │   └── Preview/
│   │   │   ├── Middleware/
│   │   │   ├── Requests/
│   │   │   │   ├── Admin/
│   │   │   │   └── Web/
│   │   │   └── Resources/Admin/V1/
│   │   ├── Jobs/
│   │   ├── Listeners/
│   │   ├── Notifications/
│   │   ├── Policies/
│   │   ├── Providers/
│   │   └── Support/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── AGENTS.md
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── lang/
│   │   ├── vi/
│   │   └── en/
│   ├── public/
│   │   ├── admin/browser/
│   │   ├── build/
│   │   └── storage -> ../storage/app/public
│   ├── resources/
│   │   ├── AGENTS.md
│   │   ├── css/
│   │   │   ├── app.css
│   │   │   ├── tokens.css
│   │   │   └── page-builder/
│   │   ├── js/
│   │   └── views/
│   │       ├── AGENTS.md
│   │       ├── layouts/
│   │       ├── pages/
│   │       ├── components/
│   │       │   ├── page-builder/
│   │       │   │   ├── layout/
│   │       │   │   ├── content/
│   │       │   │   ├── media/
│   │       │   │   ├── business/
│   │       │   │   └── forms/
│   │       │   └── shared/
│   │       ├── partials/
│   │       ├── preview/
│   │       └── errors/
│   ├── routes/
│   │   ├── web.php
│   │   ├── api.php
│   │   ├── admin.php
│   │   ├── preview.php
│   │   └── console.php
│   ├── storage/
│   └── tests/
│       ├── AGENTS.md
│       ├── Feature/
│       ├── Unit/
│       └── Architecture/
├── Admin/
│   ├── AGENTS.md
│   ├── angular.json
│   ├── package.json
│   ├── public/
│   ├── src/
│   │   ├── app/
│   │   │   ├── AGENTS.md
│   │   │   ├── core/
│   │   │   │   ├── auth/
│   │   │   │   ├── guards/
│   │   │   │   ├── interceptors/
│   │   │   │   ├── layout/
│   │   │   │   ├── services/
│   │   │   │   └── state/
│   │   │   ├── shared/
│   │   │   │   ├── components/
│   │   │   │   ├── directives/
│   │   │   │   ├── models/
│   │   │   │   ├── pipes/
│   │   │   │   └── utils/
│   │   │   ├── features/
│   │   │   │   ├── dashboard/
│   │   │   │   ├── identity/
│   │   │   │   ├── settings/
│   │   │   │   ├── media/
│   │   │   │   │   └── AGENTS.md
│   │   │   │   ├── page-builder/
│   │   │   │   │   └── AGENTS.md
│   │   │   │   ├── products/
│   │   │   │   ├── crop-solutions/
│   │   │   │   ├── services/
│   │   │   │   ├── transportation/
│   │   │   │   ├── warehouses/
│   │   │   │   ├── leads/
│   │   │   │   ├── content/
│   │   │   │   ├── showcase/
│   │   │   │   ├── seo/
│   │   │   │   ├── analytics/
│   │   │   │   └── audit/
│   │   │   ├── app.config.ts
│   │   │   └── app.routes.ts
│   │   ├── environments/
│   │   ├── styles/
│   │   └── main.ts
│   ├── tools/
│   └── tests/
├── Template/
│   ├── AGENTS.md
│   └── README_PLACE_ADMIN_TEMPLATE_HERE.md
├── FrontEndTemplate/
│   ├── AGENTS.md
│   └── README_PLACE_FRONTEND_TEMPLATE_HERE.md
├── SourceIntegrations/
│   ├── AGENTS.md
│   └── StayHubMedia/
│       ├── AGENTS.md
│       └── README_PLACE_MEDIA_SOURCE_HERE.md
├── prompts/
│   ├── AGENTS.md
│   ├── PROMPT_INDEX.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── DANH_SACH_PROMPT_CHI_TIET_00_56.md
│   ├── FULL_PROMPT_SEQUENCE.md
│   ├── 00_....md
│   └── 56_....md
├── docs/
│   ├── AGENTS.md
│   ├── PROJECT_CHARTER.md
│   ├── TECH_STACK_LOCK.md
│   ├── ARCHITECTURE.md
│   ├── DIRECTORY_TREE.md
│   ├── DATABASE_BLUEPRINT.md
│   ├── API_CONVENTIONS.md
│   ├── PAGE_BUILDER_CONTRACT.md
│   ├── MEDIA_CLONE_CHECKLIST.md
│   ├── SEO_REQUIREMENTS.md
│   ├── SECURITY_BASELINE.md
│   ├── TESTING_STRATEGY.md
│   ├── DEPLOYMENT_RUNBOOK.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── IMPLEMENTATION_GUIDE_FROM_SCRATCH.md
│   ├── EXTERNAL_SOURCE_GATES.md
│   ├── CODEX_WORKFLOW.md
│   ├── CODEX_STATE.md
│   ├── TASK_LEDGER.md
│   └── DECISIONS.md
├── docker/
│   ├── AGENTS.md
│   ├── nginx/
│   ├── php/
│   ├── supervisor/
│   └── mysql/
├── scripts/
│   ├── AGENTS.md
│   ├── README.md
│   ├── build-admin.ps1
│   ├── build-admin.sh
│   ├── check-table-prefix.php
│   └── verify-project.*
└── .github/
    └── workflows/
        ├── backend-ci.yml
        ├── admin-ci.yml
        └── security-ci.yml
```

## Ghi chú bootstrap

`BackEnd/` và `Admin/` đã có `AGENTS.md`, do đó trình cài đặt framework có thể từ chối thư mục không rỗng. Prompt bootstrap yêu cầu tạo framework trong thư mục tạm rồi merge vào đúng đích, giữ nguyên các file hướng dẫn.
