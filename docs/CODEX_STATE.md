# CODEX STATE

```yaml
project: HongVan Website Platform
company: Công Ty TNHH DV VT Hồng Vân
current_prompt: P04
last_completed_prompt: 04
status: DONE
admin_template_gate: READY
frontend_template_gate: MISSING
stayhub_media_gate: MISSING
backend_bootstrapped: true
admin_bootstrapped: false
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
latest_admin_test: not_run_p03_tooling_only
latest_admin_build: not_run_p03_tooling_only
latest_e2e: not_run_p03_tooling_only
open_blockers:
  - Admin template uses Angular 20.1.3; TECH_STACK_LOCK targets Angular 22.1.x.
  - Admin template is missing package-lock.json, public/ assets and a root license file.
  - Public frontend template source is missing at FrontEndTemplate/.
  - StayHub media reference source is missing at SourceIntegrations/StayHubMedia/.
next_prompt: 05_BOOTSTRAP_ANGULAR_22_ADMIN
```

Codex phải cập nhật file này sau mỗi prompt nhưng giữ ngắn gọn.
