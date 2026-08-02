# CODEX STATE

```yaml
project: HongVan Website Platform
company: Công Ty TNHH DV VT Hồng Vân
current_prompt: P02
last_completed_prompt: 02
status: DONE
admin_template_gate: READY
frontend_template_gate: MISSING
stayhub_media_gate: MISSING
backend_bootstrapped: false
admin_bootstrapped: false
database_migrated: false
local_domain: hongvan.local
local_domain_status: PENDING_WAMP_RECONFIGURATION
frontend_template_reminder: REQUIRED_BEFORE_P19
latest_backend_test: not_run_p02_docs_only
latest_admin_test: not_run_p02_docs_only
latest_admin_build: not_run_p02_docs_only
latest_e2e: not_run_p02_docs_only
open_blockers:
  - PHP runtime is 8.4.1; TECH_STACK_LOCK requires PHP 8.5.x before P04.
  - Admin template uses Angular 20.1.3; TECH_STACK_LOCK targets Angular 22.1.x.
  - Admin template is missing package-lock.json, public/ assets and a root license file.
  - Public frontend template source is missing at FrontEndTemplate/.
  - StayHub media reference source is missing at SourceIntegrations/StayHubMedia/.
  - Existing WAMP hongvan.local entry must be recreated for this project at the local-runtime setup checkpoint.
next_prompt: 03_REPOSITORY_HYGIENE_AND_DEVELOPER_BASELINE
```

Codex phải cập nhật file này sau mỗi prompt nhưng giữ ngắn gọn.
