# TASK INDEX — 240 TASK

| # | ID | Phase | Bao phủ | Task | File | Phụ thuộc |
|---:|---|---|---|---|---|---|
| 1 | T001 | A | `P00` | Đối chiếu HEAD hiện tại và trạng thái dự án | `tasks/T001_RECONCILE_CURRENT_HEAD_AND_STATE.md` | - |
| 2 | T002 | A | `P00,P01` | Kiểm kê cấu trúc repository và source boundaries | `tasks/T002_INVENTORY_REPOSITORY_STRUCTURE.md` | T001 |
| 3 | T003 | A | `P00,P03` | Kiểm tra hệ thống AGENTS.md và phạm vi quy tắc | `tasks/T003_VERIFY_AGENTS_RULE_HIERARCHY.md` | T002 |
| 4 | T004 | A | `P01,P03` | Đóng dấu và bảo vệ source tham chiếu chỉ đọc | `tasks/T004_FINGERPRINT_READONLY_SOURCES.md` | T003 |
| 5 | T005 | A | `P00-P56` | Đối chiếu prompt gốc, commit và task ledger | `tasks/T005_RECONCILE_PROMPTS_LEDGER.md` | T004 |
| 6 | T006 | A | `P02` | Rà kiến trúc tổng thể và ADR | `tasks/T006_REVIEW_ARCHITECTURE_AND_ADRS.md` | T005 |
| 7 | T007 | A | `P02,P04,P05` | Xác minh baseline PHP Laravel Angular Node MySQL Redis | `tasks/T007_VERIFY_TECHNOLOGY_BASELINE.md` | T006 |
| 8 | T008 | A | `P03,P04` | Rà soát hợp đồng biến môi trường | `tasks/T008_AUDIT_ENVIRONMENT_CONTRACTS.md` | T007 |
| 9 | T009 | A | `P03,P50` | Rà Git hygiene, file ignore và lịch sử secret | `tasks/T009_AUDIT_GIT_HYGIENE_AND_SECRETS.md` | T008 |
| 10 | T010 | A | `P04-P07` | Xác minh runtime WAMP và domain hongvan.local | `tasks/T010_VERIFY_LOCAL_WAMP_RUNTIME.md` | T009 |
| 11 | T011 | A | `P04-P07,P48-P50` | Chuẩn hóa danh mục lệnh test, lint, build và E2E | `tasks/T011_INVENTORY_TEST_AND_BUILD_COMMANDS.md` | T010 |
| 12 | T012 | A | `P00-P03` | Cổng nghiệm thu baseline và governance | `tasks/T012_BASELINE_GOVERNANCE_GATE.md` | T011 |
| 13 | T013 | B | `P04` | Rà soát Laravel bootstrap và service providers | `tasks/T013_AUDIT_LARAVEL_BOOTSTRAP.md` | T012 |
| 14 | T014 | B | `P05` | Rà soát Angular bootstrap, standalone và strict mode | `tasks/T014_AUDIT_ANGULAR_BOOTSTRAP.md` | T013 |
| 15 | T015 | B | `P07` | Ổn định build Angular và đồng bộ Laravel | `tasks/T015_VERIFY_MONOREPO_BUILD_SYNC.md` | T014 |
| 16 | T016 | B | `P08` | Cưỡng chế tiền tố hongvan_ cho mọi bảng | `tasks/T016_ENFORCE_DATABASE_PREFIX.md` | T015 |
| 17 | T017 | B | `P08` | Cưỡng chế comment cho mọi bảng và cột | `tasks/T017_ENFORCE_DATABASE_COMMENTS.md` | T016 |
| 18 | T018 | B | `P08` | Kiểm thử fresh, rollback và remigrate | `tasks/T018_VERIFY_MIGRATION_LIFECYCLE.md` | T017 |
| 19 | T019 | B | `P08,P09` | Rà mapping model, relation và public_id | `tasks/T019_AUDIT_MODEL_TABLE_AND_PUBLIC_IDS.md` | T018 |
| 20 | T020 | B | `P09` | Chuẩn hóa response envelope và pagination | `tasks/T020_STANDARDIZE_API_ENVELOPE.md` | T019 |
| 21 | T021 | B | `P09,P15` | Làm cứng exception renderer API | `tasks/T021_HARDEN_API_EXCEPTION_RENDERING.md` | T020 |
| 22 | T022 | B | `P09,P15` | Xác minh request ID và log context | `tasks/T022_VERIFY_REQUEST_ID_AND_LOG_CONTEXT.md` | T021 |
| 23 | T023 | B | `P09,P14` | Chuẩn hóa locale API và timezone | `tasks/T023_STANDARDIZE_LOCALE_AND_TIMEZONE.md` | T022 |
| 24 | T024 | B | `P09` | Làm cứng filter, sort, search và pagination allowlist | `tasks/T024_HARDEN_QUERY_ALLOWLIST.md` | T023 |
| 25 | T025 | B | `P09,P10,P15` | Rà toàn bộ rate limiter | `tasks/T025_REVIEW_RATE_LIMITERS.md` | T024 |
| 26 | T026 | B | `P15` | Rà security headers, trusted host và proxy | `tasks/T026_REVIEW_SECURITY_HEADERS.md` | T025 |
| 27 | T027 | B | `P04,P15` | Xác minh queue, cache và Redis contract | `tasks/T027_VERIFY_QUEUE_CACHE_REDIS.md` | T026 |
| 28 | T028 | B | `P09` | Làm cứng health và system ping | `tasks/T028_HARDEN_HEALTH_ENDPOINTS.md` | T027 |
| 29 | T029 | B | `P04,P05,P48,P49` | Thiết lập cổng Pint, PHPStan, ESLint và TypeScript | `tasks/T029_FOUNDATION_STATIC_ANALYSIS_GATE.md` | T028 |
| 30 | T030 | B | `P04-P09` | Cổng tích hợp nền tảng | `tasks/T030_FOUNDATION_INTEGRATION_GATE.md` | T029 |
| 31 | T031 | C | `P10` | Làm cứng Sanctum same-origin và CSRF | `tasks/T031_HARDEN_SANCTUM_SAME_ORIGIN.md` | T030 |
| 32 | T032 | C | `P10` | Ổn định login, logout và vòng đời session | `tasks/T032_STABILIZE_LOGIN_LOGOUT_SESSIONS.md` | T031 |
| 33 | T033 | C | `P10` | Làm cứng quên và đặt lại mật khẩu | `tasks/T033_HARDEN_FORGOT_RESET_PASSWORD.md` | T032 |
| 34 | T034 | C | `P10,P11` | Hoàn thiện khóa tài khoản và thu hồi phiên | `tasks/T034_IMPLEMENT_ACCOUNT_LOCK_AND_REVOKE.md` | T033 |
| 35 | T035 | C | `P10` | Cổng kiểm thử authentication | `tasks/T035_AUTHENTICATION_TEST_GATE.md` | T034 |
| 36 | T036 | C | `P11` | Đối chiếu registry permission với route và UI | `tasks/T036_RECONCILE_PERMISSION_REGISTRY.md` | T035 |
| 37 | T037 | C | `P11` | Ổn định quản lý role | `tasks/T037_STABILIZE_ROLE_MANAGEMENT.md` | T036 |
| 38 | T038 | C | `P11` | Ổn định quản lý user | `tasks/T038_STABILIZE_USER_MANAGEMENT.md` | T037 |
| 39 | T039 | C | `P11,P15` | Rà Policy, Gate và permission middleware | `tasks/T039_AUDIT_POLICIES_AND_MIDDLEWARE.md` | T038 |
| 40 | T040 | C | `P11` | Làm cứng permission override và Super Admin | `tasks/T040_HARDEN_PERMISSION_OVERRIDES.md` | T039 |
| 41 | T041 | C | `P12` | Ổn định schema và API user preferences | `tasks/T041_STABILIZE_PREFERENCE_SCHEMA_API.md` | T040 |
| 42 | T042 | C | `P12` | Xác minh theme preferences theo user | `tasks/T042_VERIFY_ADMIN_THEME_PREFERENCES.md` | T041 |
| 43 | T043 | C | `P12,P14` | Xác minh locale Admin theo user | `tasks/T043_VERIFY_LOCALE_PREFERENCES.md` | T042 |
| 44 | T044 | C | `P12,P17` | Ổn định menu yêu thích và điều hướng Annular | `tasks/T044_STABILIZE_FAVORITE_NAVIGATION.md` | T043 |
| 45 | T045 | C | `P13` | Làm cứng registry cấu hình công ty | `tasks/T045_HARDEN_COMPANY_SETTING_REGISTRY.md` | T044 |
| 46 | T046 | C | `P13,P15` | Làm cứng cấu hình bí mật | `tasks/T046_HARDEN_SECRET_SETTINGS.md` | T045 |
| 47 | T047 | C | `P13` | Ổn định chi nhánh và giờ làm việc | `tasks/T047_STABILIZE_BRANCHES_BUSINESS_HOURS.md` | T046 |
| 48 | T048 | C | `P13` | Ổn định social links và kênh liên hệ | `tasks/T048_STABILIZE_SOCIAL_CONTACT_CHANNELS.md` | T047 |
| 49 | T049 | C | `P14` | Hoàn thiện ngôn ngữ, translation và localized slugs | `tasks/T049_COMPLETE_LOCALIZATION_FOUNDATION.md` | T048 |
| 50 | T050 | C | `P10-P15` | Cổng tích hợp Identity, Settings, Localization và Audit | `tasks/T050_IDENTITY_SETTINGS_AUDIT_GATE.md` | T049 |
| 51 | T051 | D | `P06` | Tái kiểm định giao diện Annular | `tasks/T051_RECERTIFY_ANNULAR_VISUAL_PARITY.md` | T050 |
| 52 | T052 | D | `P06,P12` | Làm cứng layout và navigation responsive | `tasks/T052_HARDEN_RESPONSIVE_LAYOUT_NAV.md` | T051 |
| 53 | T053 | D | `P12,P17` | Ổn định header, breadcrumb, icon và favorites | `tasks/T053_STABILIZE_HEADER_BREADCRUMB_FAVORITES.md` | T052 |
| 54 | T054 | D | `P06,P11,P12` | Cổng route, permission và i18n của Admin | `tasks/T054_ADMIN_ROUTE_PERMISSION_I18N_GATE.md` | T053 |
| 55 | T055 | D | `P16` | Rà schema và storage Media | `tasks/T055_AUDIT_MEDIA_SCHEMA_STORAGE.md` | T054 |
| 56 | T056 | D | `P16,P15` | Làm cứng kiểm tra upload | `tasks/T056_HARDEN_MEDIA_UPLOAD_INSPECTION.md` | T055 |
| 57 | T057 | D | `P16` | Ổn định job tạo thumbnail WebP AVIF | `tasks/T057_STABILIZE_MEDIA_VARIANT_JOBS.md` | T056 |
| 58 | T058 | D | `P16,P17` | Ổn định thư mục Media | `tasks/T058_STABILIZE_MEDIA_FOLDERS.md` | T057 |
| 59 | T059 | D | `P17` | Ổn định metadata, visibility và lock Media | `tasks/T059_STABILIZE_MEDIA_METADATA_VISIBILITY_LOCK.md` | T058 |
| 60 | T060 | D | `P16` | Làm cứng Media usage registry | `tasks/T060_HARDEN_MEDIA_USAGE_TRACKING.md` | T059 |
| 61 | T061 | D | `P16,P17` | Ổn định trash, restore và xóa vĩnh viễn | `tasks/T061_STABILIZE_MEDIA_TRASH_RESTORE_DELETE.md` | T060 |
| 62 | T062 | D | `P16` | Làm cứng retry và cleanup Media | `tasks/T062_HARDEN_MEDIA_RETRY_CLEANUP.md` | T061 |
| 63 | T063 | D | `P16` | Làm cứng endpoint stream nội dung Media | `tasks/T063_HARDEN_MEDIA_CONTENT_STREAMING.md` | T062 |
| 64 | T064 | D | `P17` | Ổn định Media Picker và image editor | `tasks/T064_STABILIZE_MEDIA_PICKER_EDITOR.md` | T063 |
| 65 | T065 | D | `P17` | Tái kiểm định parity với StayHub Media | `tasks/T065_RECERTIFY_STAYHUB_MEDIA_PARITY.md` | T064 |
| 66 | T066 | D | `P06,P16,P17` | Cổng cuối Media và Admin shell | `tasks/T066_MEDIA_ADMIN_FINAL_GATE.md` | T065 |
| 67 | T067 | E | `P18` | Làm cứng Blade public shell | `tasks/T067_HARDEN_PUBLIC_BLADE_SHELL.md` | T066 |
| 68 | T068 | E | `P18` | Chuẩn hóa component và design token public | `tasks/T068_STANDARDIZE_PUBLIC_COMPONENTS_TOKENS.md` | T067 |
| 69 | T069 | E | `P19` | Kiểm kê lại FrontEndTemplate và giấy phép | `tasks/T069_REAUDIT_FRONTEND_TEMPLATE.md` | T068 |
| 70 | T070 | E | `P19` | Tái kiểm định header, home và footer public | `tasks/T070_RECERTIFY_PUBLIC_HEADER_HOME_FOOTER.md` | T069 |
| 71 | T071 | E | `P18` | Làm cứng legal, 404 và 500 pages | `tasks/T071_HARDEN_LEGAL_ERROR_PAGES.md` | T070 |
| 72 | T072 | E | `P14,P18` | Làm cứng route locale public | `tasks/T072_HARDEN_PUBLIC_LOCALE_ROUTING.md` | T071 |
| 73 | T073 | E | `P18,P19` | Làm cứng Vite asset và cache headers | `tasks/T073_HARDEN_PUBLIC_ASSET_PIPELINE.md` | T072 |
| 74 | T074 | E | `P19,P46` | Cổng responsive và visual public foundation | `tasks/T074_PUBLIC_RESPONSIVE_VISUAL_GATE.md` | T073 |
| 75 | T075 | E | `P20` | Rà bảng và version Theme | `tasks/T075_AUDIT_THEME_TABLES_VERSIONS.md` | T074 |
| 76 | T076 | E | `P20` | Làm cứng schema token Theme | `tasks/T076_HARDEN_THEME_TOKEN_SCHEMA.md` | T075 |
| 77 | T077 | E | `P20` | Làm cứng CSS compiler và runtime Theme | `tasks/T077_HARDEN_THEME_CSS_COMPILER.md` | T076 |
| 78 | T078 | E | `P20` | Ổn định draft, preview, publish và rollback Theme | `tasks/T078_STABILIZE_THEME_LIFECYCLE.md` | T077 |
| 79 | T079 | E | `P20` | Ổn định Angular Theme Studio | `tasks/T079_STABILIZE_THEME_STUDIO_ADMIN.md` | T078 |
| 80 | T080 | E | `P18-P20` | Cổng cuối Public foundation và Theme | `tasks/T080_PUBLIC_THEME_FINAL_GATE.md` | T079 |
| 81 | T081 | F | `P21` | Rà schema database Page Builder | `tasks/T081_AUDIT_PAGE_BUILDER_SCHEMA.md` | T080 |
| 82 | T082 | F | `P21` | Làm cứng PageDocument schema và checksum | `tasks/T082_HARDEN_PAGE_DOCUMENT_SCHEMA.md` | T081 |
| 83 | T083 | F | `P21` | Làm cứng BlockRegistry metadata | `tasks/T083_HARDEN_BLOCK_REGISTRY.md` | T082 |
| 84 | T084 | F | `P21` | Làm cứng PageDocumentValidator | `tasks/T084_HARDEN_PAGE_VALIDATOR.md` | T083 |
| 85 | T085 | F | `P21` | Làm cứng block/document migrator | `tasks/T085_HARDEN_BLOCK_MIGRATOR.md` | T084 |
| 86 | T086 | F | `P21,P22` | Làm cứng server PageDocumentRenderer | `tasks/T086_HARDEN_SERVER_RENDERER.md` | T085 |
| 87 | T087 | F | `P21,P24` | Chuẩn hóa cache key và dependency tags | `tasks/T087_STANDARDIZE_PAGE_CACHE_KEYS.md` | T086 |
| 88 | T088 | F | `P22` | Tái kiểm định 7 layout blocks | `tasks/T088_RECERTIFY_LAYOUT_BLOCK_DEFINITIONS.md` | T087 |
| 89 | T089 | F | `P22` | Làm cứng renderer và class resolver layout | `tasks/T089_HARDEN_LAYOUT_RENDERERS_CLASSES.md` | T088 |
| 90 | T090 | F | `P23` | Tái kiểm định content blocks | `tasks/T090_RECERTIFY_CONTENT_BLOCK_DEFINITIONS.md` | T089 |
| 91 | T091 | F | `P23,P15` | Làm cứng sanitizer content/media blocks | `tasks/T091_HARDEN_CONTENT_SANITIZER.md` | T090 |
| 92 | T092 | F | `P23` | Làm cứng Media resolver cho Page Builder | `tasks/T092_HARDEN_PAGE_MEDIA_RESOLVER.md` | T091 |
| 93 | T093 | F | `P23` | Làm cứng đồng bộ usage Page Builder | `tasks/T093_HARDEN_PAGE_MEDIA_USAGE_SYNC.md` | T092 |
| 94 | T094 | F | `P24` | Tái kiểm định DataSourceRegistry | `tasks/T094_RECERTIFY_DYNAMIC_DATA_SOURCES.md` | T093 |
| 95 | T095 | F | `P24` | Làm cứng dynamic loader, memoization và cache | `tasks/T095_HARDEN_DYNAMIC_LOADER_CACHE.md` | T094 |
| 96 | T096 | F | `P25` | Tái kiểm định registry Form blocks | `tasks/T096_RECERTIFY_FORM_REGISTRY.md` | T095 |
| 97 | T097 | F | `P25` | Làm cứng signed context Form blocks | `tasks/T097_HARDEN_SIGNED_FORM_CONTEXT.md` | T096 |
| 98 | T098 | F | `P25,P38` | Làm cứng Blade form và tích hợp Lead | `tasks/T098_HARDEN_FORM_RENDER_LEAD_INTEGRATION.md` | T097 |
| 99 | T099 | F | `P26` | Ổn định models và data-access Page Builder Angular | `tasks/T099_STABILIZE_EDITOR_DATA_MODELS.md` | T098 |
| 100 | T100 | F | `P26` | Làm cứng thao tác document immutable | `tasks/T100_HARDEN_DOCUMENT_MUTATIONS.md` | T099 |
| 101 | T101 | F | `P26` | Ổn định history, undo và redo | `tasks/T101_STABILIZE_HISTORY_UNDO_REDO.md` | T100 |
| 102 | T102 | F | `P26` | Ổn định editor shell, tree và palette | `tasks/T102_STABILIZE_EDITOR_SHELL_TREE_PALETTE.md` | T101 |
| 103 | T103 | F | `P26` | Ổn định inspector và responsive settings | `tasks/T103_STABILIZE_INSPECTOR_RESPONSIVE.md` | T102 |
| 104 | T104 | F | `P26` | Làm cứng orchestration autosave cơ bản | `tasks/T104_HARDEN_AUTOSAVE_BASELINE.md` | T103 |
| 105 | T105 | F | `P27` | Làm cứng preview session và iframe protocol | `tasks/T105_HARDEN_PREVIEW_BACKEND_IFRAME.md` | T104 |
| 106 | T106 | F | `P21-P27` | Cổng Page Builder foundation P21-P27 | `tasks/T106_PAGE_BUILDER_FOUNDATION_GATE.md` | T105 |
| 107 | T107 | G | `P28` | Audit toàn bộ lifecycle versioning và publishing P28 | `tasks/T107_AUDIT_P28_LIFECYCLE.md` | T106 |
| 108 | T108 | G | `P28,P29` | Làm cứng optimistic concurrency autosave | `tasks/T108_HARDEN_AUTOSAVE_CONCURRENCY.md` | T107 |
| 109 | T109 | G | `P28` | Ổn định manual save và immutable milestones | `tasks/T109_STABILIZE_MANUAL_MILESTONES.md` | T108 |
| 110 | T110 | G | `P28` | Hoàn thiện API và Resource lịch sử version | `tasks/T110_COMPLETE_VERSION_APIS.md` | T109 |
| 111 | T111 | G | `P28` | Triển khai diff summary theo block ID | `tasks/T111_IMPLEMENT_VERSION_DIFF_ENGINE.md` | T110 |
| 112 | T112 | G | `P28` | Làm cứng transaction Publish now | `tasks/T112_HARDEN_PUBLISH_TRANSACTION.md` | T111 |
| 113 | T113 | G | `P28` | Làm cứng validation trước publish | `tasks/T113_HARDEN_PREPUBLISH_VALIDATION.md` | T112 |
| 114 | T114 | G | `P28` | Ổn định dữ liệu lịch publish theo UTC | `tasks/T114_STABILIZE_SCHEDULE_DATA_UTC.md` | T113 |
| 115 | T115 | G | `P28` | Làm cứng scheduler chống race và chạy trùng | `tasks/T115_HARDEN_SCHEDULER_IDEMPOTENCY.md` | T114 |
| 116 | T116 | G | `P28` | Hoàn thiện cancel và reschedule | `tasks/T116_IMPLEMENT_CANCEL_RESCHEDULE.md` | T115 |
| 117 | T117 | G | `P28` | Hoàn thiện unpublish và archive page | `tasks/T117_IMPLEMENT_UNPUBLISH_ARCHIVE.md` | T116 |
| 118 | T118 | G | `P28` | Làm cứng rollback bằng revision mới | `tasks/T118_HARDEN_ROLLBACK.md` | T117 |
| 119 | T119 | G | `P28` | Chuẩn hóa cache, sitemap và audit sau commit | `tasks/T119_HARDEN_AFTER_COMMIT_EFFECTS.md` | T118 |
| 120 | T120 | G | `P28` | Thay native prompt/confirm bằng Material dialogs | `tasks/T120_REPLACE_NATIVE_P28_DIALOGS.md` | T119 |
| 121 | T121 | G | `P28` | Triển khai UI giải quyết conflict | `tasks/T121_IMPLEMENT_CONFLICT_RESOLUTION_UI.md` | T120 |
| 122 | T122 | G | `P28` | Ổn định history và diff UI | `tasks/T122_STABILIZE_VERSION_HISTORY_DIFF_UI.md` | T121 |
| 123 | T123 | G | `P28` | Ổn định UI schedule và timezone | `tasks/T123_STABILIZE_SCHEDULE_UI_TIMEZONE.md` | T122 |
| 124 | T124 | G | `P29` | Ổn định thư viện template P29 | `tasks/T124_STABILIZE_TEMPLATE_LIBRARY.md` | T123 |
| 125 | T125 | G | `P29` | Hoàn thiện import, export và duplicate P29 | `tasks/T125_COMPLETE_IMPORT_EXPORT_DUPLICATE.md` | T124 |
| 126 | T126 | G | `P28,P29` | Làm cứng edit lock và cổng regression P28-P29 | `tasks/T126_HARDEN_EDIT_LOCKS_AND_REGRESSION.md` | T125 |
| 127 | T127 | H | `P30` | Thiết kế và triển khai schema Menu | `tasks/T127_DESIGN_MENU_SCHEMA_MODELS.md` | T126 |
| 128 | T128 | H | `P30` | Triển khai API, validation và Policy Menu | `tasks/T128_IMPLEMENT_MENU_API_POLICIES.md` | T127 |
| 129 | T129 | H | `P30` | Triển khai Angular quản lý Menu | `tasks/T129_IMPLEMENT_MENU_ADMIN_UI.md` | T128 |
| 130 | T130 | H | `P30` | Làm cứng nesting, link và localization Menu | `tasks/T130_HARDEN_MENU_NESTING_LOCALIZATION.md` | T129 |
| 131 | T131 | H | `P30` | Thiết kế schema versioned Global Regions | `tasks/T131_DESIGN_GLOBAL_REGION_SCHEMA.md` | T130 |
| 132 | T132 | H | `P30` | Triển khai API và lifecycle Global Regions | `tasks/T132_IMPLEMENT_GLOBAL_REGION_API.md` | T131 |
| 133 | T133 | H | `P30` | Triển khai Angular editor Global Regions | `tasks/T133_IMPLEMENT_GLOBAL_REGION_EDITOR.md` | T132 |
| 134 | T134 | H | `P30` | Render header/footer/global regions trên public | `tasks/T134_RENDER_HEADER_FOOTER_REGIONS.md` | T133 |
| 135 | T135 | H | `P31` | Thiết kế registry route public | `tasks/T135_DESIGN_PUBLIC_ROUTE_REGISTRY.md` | T134 |
| 136 | T136 | H | `P31` | Triển khai resolver localized slug | `tasks/T136_IMPLEMENT_LOCALIZED_SLUG_RESOLVER.md` | T135 |
| 137 | T137 | H | `P31` | Triển khai route public động cho Page Builder | `tasks/T137_IMPLEMENT_PAGE_BUILDER_PUBLIC_ROUTES.md` | T136 |
| 138 | T138 | H | `P31,P43` | Làm cứng collision, canonical và redirect | `tasks/T138_HARDEN_ROUTE_COLLISIONS_CANONICAL.md` | T137 |
| 139 | T139 | H | `P31` | Hoàn thiện các core page public | `tasks/T139_IMPLEMENT_CORE_PUBLIC_PAGES.md` | T138 |
| 140 | T140 | H | `P31` | Cô lập draft, preview và published public | `tasks/T140_ISOLATE_DRAFT_PREVIEW_PUBLISHED.md` | T139 |
| 141 | T141 | H | `P30,P31` | Làm cứng cache invalidation Menu và Regions | `tasks/T141_HARDEN_MENU_REGION_CACHE.md` | T140 |
| 142 | T142 | H | `P30,P31` | Cổng E2E Menu, Regions và Public Routing | `tasks/T142_PUBLIC_ROUTING_E2E_GATE.md` | T141 |
| 143 | T143 | I | `P32,P33` | Rà schema và domain Product | `tasks/T143_AUDIT_PRODUCT_SCHEMA_DOMAIN.md` | T142 |
| 144 | T144 | I | `P32` | Làm cứng pricing modes và resolver | `tasks/T144_HARDEN_PRODUCT_PRICING.md` | T143 |
| 145 | T145 | I | `P32,P33` | Ổn định category, brand, tag và attribute | `tasks/T145_STABILIZE_PRODUCT_TAXONOMIES.md` | T144 |
| 146 | T146 | I | `P33` | Ổn định Product manager và Admin API | `tasks/T146_STABILIZE_PRODUCT_MANAGER_API.md` | T145 |
| 147 | T147 | I | `P33` | Ổn định Angular danh sách Product | `tasks/T147_STABILIZE_PRODUCT_LIST_UI.md` | T146 |
| 148 | T148 | I | `P33` | Ổn định Angular editor Product | `tasks/T148_STABILIZE_PRODUCT_EDITOR_UI.md` | T147 |
| 149 | T149 | I | `P32,P33` | Làm cứng media, specifications và related products | `tasks/T149_HARDEN_PRODUCT_MEDIA_SPEC_RELATED.md` | T148 |
| 150 | T150 | I | `P33` | Làm cứng bulk, publish, archive, trash và restore | `tasks/T150_HARDEN_PRODUCT_BULK_LIFECYCLE.md` | T149 |
| 151 | T151 | I | `P33` | Triển khai query catalog public | `tasks/T151_IMPLEMENT_PUBLIC_PRODUCT_QUERY.md` | T150 |
| 152 | T152 | I | `P33,P31` | Triển khai route category, brand và tag public | `tasks/T152_IMPLEMENT_PRODUCT_TAXONOMY_ROUTES.md` | T151 |
| 153 | T153 | I | `P33` | Triển khai detail, giá và CTA báo giá | `tasks/T153_IMPLEMENT_PRODUCT_DETAIL_PRICE_QUOTE.md` | T152 |
| 154 | T154 | I | `P33,P42` | Triển khai SEO và structured data Product | `tasks/T154_IMPLEMENT_PRODUCT_SEO_SCHEMA.md` | T153 |
| 155 | T155 | I | `P24,P33` | Đăng ký Product blocks và data sources | `tasks/T155_REGISTER_PRODUCT_PAGE_BUILDER_BLOCKS.md` | T154 |
| 156 | T156 | I | `P32,P33` | Cổng test/E2E Product | `tasks/T156_PRODUCT_FINAL_TEST_GATE.md` | T155 |
| 157 | T157 | J | `P34` | Rà schema và hierarchy Crop | `tasks/T157_AUDIT_CROP_SCHEMA_HIERARCHY.md` | T156 |
| 158 | T158 | J | `P34` | Ổn định manager và API Crop Solutions | `tasks/T158_STABILIZE_CROP_MANAGER_API.md` | T157 |
| 159 | T159 | J | `P34` | Ổn định Angular Crop Solutions | `tasks/T159_STABILIZE_CROP_ADMIN_UI.md` | T158 |
| 160 | T160 | J | `P34,P31` | Triển khai public query và routes Crop | `tasks/T160_IMPLEMENT_PUBLIC_CROP_ROUTES.md` | T159 |
| 161 | T161 | J | `P34` | Hoàn thiện timeline giai đoạn cây trồng | `tasks/T161_IMPLEMENT_CROP_STAGE_TIMELINE.md` | T160 |
| 162 | T162 | J | `P24,P34,P42` | Đăng ký Crop blocks, SEO và internal links | `tasks/T162_REGISTER_CROP_BLOCKS_SEO_LINKS.md` | T161 |
| 163 | T163 | J | `P34` | Cổng test/E2E Crop Solutions | `tasks/T163_CROP_FINAL_TEST_GATE.md` | T162 |
| 164 | T164 | J | `P35` | Rà schema Service và boundary chuyên ngành | `tasks/T164_AUDIT_SERVICE_SCHEMA_BOUNDARY.md` | T163 |
| 165 | T165 | J | `P35` | Ổn định manager và API Service | `tasks/T165_STABILIZE_SERVICE_MANAGER_API.md` | T164 |
| 166 | T166 | J | `P35` | Ổn định Angular Services | `tasks/T166_STABILIZE_SERVICE_ADMIN_UI.md` | T165 |
| 167 | T167 | J | `P35,P31` | Triển khai public list/detail và CTA Service | `tasks/T167_IMPLEMENT_PUBLIC_SERVICE_ROUTES.md` | T166 |
| 168 | T168 | J | `P24,P35,P42` | Đăng ký Service blocks và SEO | `tasks/T168_REGISTER_SERVICE_BLOCKS_SEO.md` | T167 |
| 169 | T169 | J | `P35` | Cổng test/E2E Services | `tasks/T169_SERVICE_FINAL_TEST_GATE.md` | T168 |
| 170 | T170 | J | `P36` | Rà schema và phạm vi Transportation | `tasks/T170_AUDIT_TRANSPORT_SCHEMA_BOUNDARY.md` | T169 |
| 171 | T171 | J | `P36` | Ổn định API fleet, routes và areas | `tasks/T171_STABILIZE_TRANSPORT_MANAGER_API.md` | T170 |
| 172 | T172 | J | `P36` | Ổn định Angular Transportation | `tasks/T172_STABILIZE_TRANSPORT_ADMIN_UI.md` | T171 |
| 173 | T173 | J | `P36,P31` | Triển khai public capability pages Transportation | `tasks/T173_IMPLEMENT_PUBLIC_TRANSPORT_PAGES.md` | T172 |
| 174 | T174 | J | `P25,P36,P38` | Làm cứng request vận chuyển và Lead integration | `tasks/T174_HARDEN_TRANSPORT_REQUEST_LEADS.md` | T173 |
| 175 | T175 | J | `P24,P36,P42` | Đăng ký Transportation blocks và SEO | `tasks/T175_REGISTER_TRANSPORT_BLOCKS_SEO.md` | T174 |
| 176 | T176 | J | `P36` | Cổng test/E2E Transportation | `tasks/T176_TRANSPORT_FINAL_TEST_GATE.md` | T175 |
| 177 | T177 | J | `P37` | Rà schema Warehouse, facilities và services | `tasks/T177_AUDIT_WAREHOUSE_SCHEMA.md` | T176 |
| 178 | T178 | J | `P37` | Ổn định manager và API Warehouse | `tasks/T178_STABILIZE_WAREHOUSE_MANAGER_API.md` | T177 |
| 179 | T179 | J | `P37` | Ổn định Angular Warehouses | `tasks/T179_STABILIZE_WAREHOUSE_ADMIN_UI.md` | T178 |
| 180 | T180 | J | `P37,P31` | Triển khai public listing và detail Warehouse | `tasks/T180_IMPLEMENT_PUBLIC_WAREHOUSE_ROUTES.md` | T179 |
| 181 | T181 | J | `P37` | Làm cứng bản đồ, giờ mở cửa và privacy | `tasks/T181_HARDEN_WAREHOUSE_MAP_HOURS_PRIVACY.md` | T180 |
| 182 | T182 | J | `P25,P37,P38` | Làm cứng request kho và Lead integration | `tasks/T182_HARDEN_WAREHOUSE_REQUEST_LEADS.md` | T181 |
| 183 | T183 | J | `P24,P37,P42` | Đăng ký Warehouse blocks, SEO và cache | `tasks/T183_REGISTER_WAREHOUSE_BLOCKS_SEO.md` | T182 |
| 184 | T184 | J | `P37` | Cổng test/E2E Warehouses | `tasks/T184_WAREHOUSE_FINAL_TEST_GATE.md` | T183 |
| 185 | T185 | K | `P38` | Rà schema Lead và dữ liệu nhạy cảm | `tasks/T185_AUDIT_LEAD_SCHEMA_ENCRYPTION.md` | T184 |
| 186 | T186 | K | `P38` | Làm cứng intake, idempotency và dedup | `tasks/T186_HARDEN_LEAD_INTAKE_DEDUP.md` | T185 |
| 187 | T187 | K | `P38` | Ổn định assignment, status và notes | `tasks/T187_STABILIZE_LEAD_WORKFLOW.md` | T186 |
| 188 | T188 | K | `P38,P45` | Làm cứng notification và follow-up Leads | `tasks/T188_HARDEN_LEAD_NOTIFICATIONS.md` | T187 |
| 189 | T189 | K | `P38` | Ổn định inbox, export và retention | `tasks/T189_STABILIZE_LEAD_ADMIN_EXPORT_RETENTION.md` | T188 |
| 190 | T190 | K | `P38` | Cổng test/E2E Lead workflows | `tasks/T190_LEAD_FINAL_TEST_GATE.md` | T189 |
| 191 | T191 | K | `P39` | Rà schema Posts, categories và tags | `tasks/T191_AUDIT_POST_SCHEMA_TAXONOMY.md` | T190 |
| 192 | T192 | K | `P39,P15` | Làm cứng RichText sanitizer Posts | `tasks/T192_HARDEN_POST_RICH_TEXT.md` | T191 |
| 193 | T193 | K | `P39` | Ổn định manager, schedule và slug history | `tasks/T193_STABILIZE_POST_MANAGER_SCHEDULE_SLUGS.md` | T192 |
| 194 | T194 | K | `P39` | Ổn định Angular CMS editor | `tasks/T194_STABILIZE_POST_ADMIN_EDITOR.md` | T193 |
| 195 | T195 | K | `P39,P31` | Triển khai public post listing, category và tag | `tasks/T195_IMPLEMENT_PUBLIC_POST_LISTINGS.md` | T194 |
| 196 | T196 | K | `P39,P42,P43` | Triển khai detail, related, RSS, blocks và SEO Posts | `tasks/T196_IMPLEMENT_POST_DETAIL_RSS_BLOCKS_SEO.md` | T195 |
| 197 | T197 | K | `P39` | Cổng test/E2E News Content | `tasks/T197_POST_FINAL_TEST_GATE.md` | T196 |
| 198 | T198 | K | `P40` | Rà schema Showcase, Media và documents | `tasks/T198_AUDIT_SHOWCASE_SCHEMA_MEDIA_DOCS.md` | T197 |
| 199 | T199 | K | `P40` | Ổn định manager và API Showcase | `tasks/T199_STABILIZE_SHOWCASE_MANAGER_API.md` | T198 |
| 200 | T200 | K | `P40` | Ổn định Angular Showcase | `tasks/T200_STABILIZE_SHOWCASE_ADMIN_UI.md` | T199 |
| 201 | T201 | K | `P40,P31,P42` | Triển khai public galleries, projects, blocks và SEO | `tasks/T201_IMPLEMENT_PUBLIC_SHOWCASE_BLOCKS_SEO.md` | T200 |
| 202 | T202 | K | `P40` | Cổng test/E2E Showcase | `tasks/T202_SHOWCASE_FINAL_TEST_GATE.md` | T201 |
| 203 | T203 | K | `P41` | Làm cứng normalization và FULLTEXT index | `tasks/T203_HARDEN_SEARCH_NORMALIZATION_INDEX.md` | T202 |
| 204 | T204 | K | `P41` | Ổn định reindex và cập nhật search index | `tasks/T204_STABILIZE_SEARCH_INDEXER.md` | T203 |
| 205 | T205 | K | `P41` | Làm cứng API search và filters | `tasks/T205_HARDEN_PUBLIC_SEARCH_API.md` | T204 |
| 206 | T206 | K | `P41,P31` | Triển khai Blade search UI | `tasks/T206_IMPLEMENT_BLADE_SEARCH_UI.md` | T205 |
| 207 | T207 | K | `P41` | Làm cứng related content và search logs | `tasks/T207_HARDEN_RELATED_SEARCH_LOGS_PRIVACY.md` | T206 |
| 208 | T208 | K | `P41` | Cổng test/E2E Search | `tasks/T208_SEARCH_FINAL_TEST_GATE.md` | T207 |
| 209 | T209 | L | `P42` | Làm cứng entity registry và meta resolver SEO | `tasks/T209_HARDEN_SEO_ENTITY_META_RESOLVER.md` | T208 |
| 210 | T210 | L | `P42` | Ổn định Angular SEO management | `tasks/T210_STABILIZE_SEO_ADMIN.md` | T209 |
| 211 | T211 | L | `P43` | Làm cứng redirect manager | `tasks/T211_HARDEN_REDIRECT_MANAGER.md` | T210 |
| 212 | T212 | L | `P43` | Làm cứng sitemap index, shards và cache | `tasks/T212_HARDEN_SITEMAP_GENERATOR.md` | T211 |
| 213 | T213 | L | `P42,P43` | Làm cứng robots, canonical và hreflang | `tasks/T213_HARDEN_ROBOTS_CANONICAL_HREFLANG.md` | T212 |
| 214 | T214 | L | `P42,P43` | Làm cứng structured data và social cards | `tasks/T214_HARDEN_STRUCTURED_SOCIAL_DATA.md` | T213 |
| 215 | T215 | L | `P42,P43` | Cổng crawl và test SEO | `tasks/T215_SEO_CRAWL_FINAL_GATE.md` | T214 |
| 216 | T216 | L | `P44` | Làm cứng consent records và API | `tasks/T216_HARDEN_CONSENT_RECORDS_API.md` | T215 |
| 217 | T217 | L | `P44` | Làm cứng providers, CSP, script renderer và banner | `tasks/T217_HARDEN_ANALYTICS_PROVIDER_CSP_UI.md` | T216 |
| 218 | T218 | L | `P44` | Cổng privacy và test Analytics | `tasks/T218_ANALYTICS_PRIVACY_FINAL_GATE.md` | T217 |
| 219 | T219 | L | `P45` | Làm cứng aggregate và visibility Dashboard | `tasks/T219_HARDEN_DASHBOARD_AGGREGATES.md` | T218 |
| 220 | T220 | L | `P45` | Làm cứng notification Dashboard | `tasks/T220_HARDEN_DASHBOARD_NOTIFICATIONS.md` | T219 |
| 221 | T221 | L | `P45` | Làm cứng reports queue và Dashboard UI | `tasks/T221_HARDEN_REPORTS_DASHBOARD_UI.md` | T220 |
| 222 | T222 | L | `P45` | Cổng test/E2E Dashboard | `tasks/T222_DASHBOARD_FINAL_TEST_GATE.md` | T221 |
| 223 | T223 | M | `P47` | Hoàn thiện seeders an toàn và idempotent | `tasks/T223_BUILD_FINAL_SAFE_SEEDERS.md` | T222 |
| 224 | T224 | M | `P48` | Tái chứng nhận QA Backend toàn hệ thống | `tasks/T224_BACKEND_QA_RECERTIFICATION.md` | T223 |
| 225 | T225 | M | `P49` | Tái chứng nhận Angular, E2E và Visual QA | `tasks/T225_ADMIN_E2E_VISUAL_RECERTIFICATION.md` | T224 |
| 226 | T226 | M | `P46` | Cổng accessibility và performance | `tasks/T226_ACCESSIBILITY_PERFORMANCE_GATE.md` | T225 |
| 227 | T227 | M | `P50,P53` | Quét dependency, license, secret và container | `tasks/T227_DEPENDENCY_LICENSE_SECURITY_SCANS.md` | T226 |
| 228 | T228 | M | `P50` | Hoàn thiện CI và delivery pipeline | `tasks/T228_FINALIZE_CI_DELIVERY_PIPELINE.md` | T227 |
| 229 | T229 | M | `P51` | Hoàn thiện Docker và production deployment | `tasks/T229_IMPLEMENT_DOCKER_PRODUCTION_DEPLOYMENT.md` | T228 |
| 230 | T230 | M | `P52` | Hoàn thiện backup, restore, monitoring và incident runbooks | `tasks/T230_IMPLEMENT_BACKUP_RESTORE_MONITORING.md` | T229 |
| 231 | T231 | M | `P53` | Security review cuối toàn hệ thống | `tasks/T231_FINAL_SECURITY_REVIEW.md` | T230 |
| 232 | T232 | M | `P54` | UAT, nội dung và dữ liệu sẵn sàng | `tasks/T232_UAT_CONTENT_DATA_READINESS.md` | T231 |
| 233 | T233 | M | `P55` | Production cutover có kiểm soát | `tasks/T233_PRODUCTION_CUTOVER.md` | T232 |
| 234 | T234 | M | `P56` | Tài liệu và bàn giao cuối | `tasks/T234_DOCUMENTATION_HANDOVER.md` | T233 |
| 235 | T235 | N | `R90` | Rà soát lại toàn bộ source sau triển khai | `tasks/T235_FULL_SOURCE_REAUDIT.md` | T234 |
| 236 | T236 | N | `R90` | Audit chéo route, API, DB, permission và i18n | `tasks/T236_AUDIT_ROUTE_API_DB_PERMISSION_I18N.md` | T235 |
| 237 | T237 | N | `R90` | Audit UI, crawl, accessibility và performance | `tasks/T237_AUDIT_UI_CRAWL_A11Y_PERFORMANCE.md` | T236 |
| 238 | T238 | N | `R90` | Audit test, security, CI và operations | `tasks/T238_AUDIT_TEST_SECURITY_CI_OPERATIONS.md` | T237 |
| 239 | T239 | N | `R90` | Sinh prompt focused cho mọi gap và lặp audit | `tasks/T239_GENERATE_AND_LOOP_GAP_PROMPTS.md` | T238 |
| 240 | T240 | N | `R99` | Release Gate cuối GO hoặc NO-GO | `tasks/T240_FINAL_RELEASE_GO_NO_GO.md` | T239 |
