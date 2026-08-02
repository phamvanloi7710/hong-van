# CODEX STATE

```yaml
project: HongVan Website Platform
company: Công Ty TNHH DV VT Hồng Vân
current_prompt: P08
last_completed_prompt: 08
status: DONE
admin_template_gate: READY
admin_template_integration: SELECTIVE_PORT_COMPLETE
frontend_template_gate: MISSING
stayhub_media_gate: MISSING
backend_bootstrapped: true
admin_bootstrapped: true
admin_build_integration: CONFIGURED_LARAVEL_ADMIN
database_migrated: true
local_domain: hongvan.local
local_domain_status: CONFIGURED_WAMP_BACKEND_PUBLIC
frontend_template_reminder: REQUIRED_BEFORE_P19
latest_prerequisite_check: passed_php_8_5_9_node_24_15_0
latest_readonly_source_check: passed_powershell_and_git_bash
latest_laravel_13_patch: 13.23.0
latest_php_85_patch: 8.5.9
latest_database_runtime: passed_mysql_8_2_0_isolated_hongvan_testing_utf8mb4_0900_ai_ci
latest_database_migration: passed_fresh_rollback_batch_remigrate_and_fresh_seed_14_prefixed_tables
latest_database_prefix_check: passed_7_php_files_and_rejected_unprefixed_and_double_prefixed_fixtures
latest_backend_test: passed_phpunit_12_tests_103_assertions_mysql
latest_backend_format: passed_pint
latest_backend_static_analysis: passed_larastan_level_6
latest_backend_build: passed_vite_7_3_6
latest_angular_core_patch: 22.1.0
latest_angular_cli_patch: 22.1.2
latest_typescript_patch: 6.0.3
latest_admin_lint: passed_angular_eslint_22_1_0
latest_admin_test: passed_vitest_4_files_9_tests
latest_admin_build: passed_angular_production_initial_312_41_kb
latest_admin_sync: passed_86_files_to_laravel_public_admin_browser
latest_e2e: passed_browser_qa_dashboard_login_theme_responsive
latest_visual_qa: passed_1280x720_and_390x844
latest_admin_smoke: passed_hongvan_local_root_deep_link_asset_cache
open_blockers:
  - Windows terminal PATH still resolves PHP 8.4.1; use WAMP PHP 8.5.9 or update PATH before backend commands.
  - Admin template is missing package-lock.json, public/ assets and a root license file.
  - Angular CLI 22.1.2 has three moderate dev-tool audit findings through MCP SDK/Hono with no compatible in-range npm fix.
  - Public frontend template source is missing at FrontEndTemplate/.
  - StayHub media reference source is missing at SourceIntegrations/StayHubMedia/.
next_prompt: 09_ADMIN_API_FOUNDATION
```

Codex phải cập nhật file này sau mỗi prompt nhưng giữ ngắn gọn.
