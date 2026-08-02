# DEPLOYMENT RUNBOOK — HỒNG VÂN

Tài liệu thao tác từ đầu đến production nằm tại:

```text
HUONG_DAN_TRIEN_KHAI_TU_DAU.md
```

Runbook này là checklist ngắn dùng ở P51–P55.

## Preconditions

- P50 build/CI DONE.
- P51 manifests/compose/nginx được tạo và test.
- P52 backup/monitoring DONE.
- P53 không còn critical/high unresolved.
- P54 UAT approved.
- `FrontEndTemplate/` và StayHub Media gate đã hoàn tất hoặc có acceptance thay thế.

## Services

- Nginx.
- PHP-FPM 8.5.
- Laravel app.
- Queue worker.
- Scheduler.
- MySQL 8.4 LTS.
- Redis.
- Optional S3-compatible object storage.
- TLS.

## Build flow

```text
Admin npm ci → lint/test → build:laravel
→ BackEnd/public/admin/browser

BackEnd composer install --no-dev --optimize-autoloader
→ public asset build
→ migrate --force
→ storage link
→ optimize/cache
→ queue restart
```

## Pre-deploy

- Lock release tag/commit and artifact checksum.
- Backup DB/media and verify restore.
- Test migrations on staging clone.
- Verify env/secrets without printing them.
- Prepare previous release and rollback commands.
- Put maintenance/minimal-downtime plan in place.

## Post-deploy smoke

- Health route.
- Public home/product/service/transport/warehouse/contact.
- `/admin` and deep links.
- Login, RBAC and user theme.
- Media Manager.
- Page Builder preview/publish.
- Lead forms and notifications.
- Queue/scheduler.
- Sitemap/robots/canonical/structured data.
- Logs, metrics, security headers and TLS.

## Rollback trigger

Rollback immediately when migration, health, auth, core public pages, lead submission, queue or security gate fails. Không vá tay ngoài Git mà không ghi nhận.
