# CODEX STATE

```yaml
project: HongVan Website Platform
company: Công Ty TNHH DV VT Hồng Vân
current_prompt: P05
last_completed_prompt: 05
status: DONE
admin_template_gate: READY
frontend_template_gate: MISSING
stayhub_media_gate: MISSING
backend_bootstrapped: true
admin_bootstrapped: true
database_migrated: false
local_domain: hongvan.local
local_domain_status: CONFIGURED_WAMP_BACKEND_PUBLIC
frontend_template_reminder: REQUIRED_BEFORE_P19
latest_prerequisite_check: passed_php_8_5_9_node_24_15_0
latest_readonly_source_check: passed_powershell_and_git_bash
latest_laravel_13_patch: 13.23.0
latest_php_85_patch: 8.5.9
latest_backend_test: passed_phpunit_3_tests_4_assertions
latest_backend_format: passed_pint
latest_backend_static_analysis: passed_larastan_level_6
latest_backend_build: passed_vite_7_3_6
latest_angular_core_patch: 22.1.0
latest_angular_cli_patch: 22.1.2
latest_typescript_patch: 6.0.3
latest_admin_lint: passed_angular_eslint_22_1_0
latest_admin_test: passed_vitest_2_files_4_tests
latest_admin_build: passed_angular_production_initial_190_41_kb
latest_e2e: not_run_p05_unit_scope_only
open_blockers:
  - P06 must port the Angular 20.1.3 template into the Angular 22.1.x target without modifying Template/.
  - Admin template is missing package-lock.json, public/ assets and a root license file.
  - Angular CLI 22.1.2 has three moderate dev-tool audit findings through MCP SDK/Hono with no compatible in-range npm fix.
  - Public frontend template source is missing at FrontEndTemplate/.
  - StayHub media reference source is missing at SourceIntegrations/StayHubMedia/.
next_prompt: 06_PORT_ADMIN_TEMPLATE
```

Codex phải cập nhật file này sau mỗi prompt nhưng giữ ngắn gọn.
