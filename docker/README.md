# HongVan local Docker

Mô hình local chỉ dùng chung `local-proxy` và external network `local-infra`. MySQL, Redis, PHP-FPM, queue, scheduler, Nginx, phpMyAdmin và volumes đều thuộc riêng Compose project `hongvan`.

## Khởi động lần đầu

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-docker-env.ps1
docker network inspect local-infra
docker compose config --quiet
docker compose build
docker compose up -d mysql redis
docker compose run --rm app php artisan migrate --force --seed
docker compose up -d
```

Không chạy `docker compose down -v` nếu cần giữ database/media. Migration không chạy tự động khi container restart.

Shared proxy phải là container `local-proxy` dùng `nginxproxy/nginx-proxy` trên network `local-infra`. Service `hongvan-nginx` tự đăng ký `hongvan.local` qua `VIRTUAL_HOST`; không publish cổng HTTP riêng ra host.

Cổng 80 phải do `local-proxy` xử lý. Nếu WAMP Apache đang chạy, dừng riêng service này trong Wampserver hoặc PowerShell chạy quyền Administrator trước khi smoke:

```powershell
Stop-Service wampapache64
```

Không cần dừng MySQL/MariaDB của WAMP vì Hồng Vân không publish MySQL ra host.

Windows hosts:

```text
127.0.0.1 hongvan.local
127.0.0.1 hongvan-pma.local
```

## Kiểm tra

```powershell
docker compose ps
powershell -ExecutionPolicy Bypass -File .\scripts\smoke.ps1
curl.exe --resolve hongvan-pma.local:80:127.0.0.1 http://hongvan-pma.local/
docker compose exec redis sh -c 'REDISCLI_AUTH="$REDIS_PASSWORD" redis-cli ping'
docker compose exec app php artisan migrate:status
```

## Dừng

```powershell
docker compose down
```

Lệnh trên giữ nguyên ba named volume `hongvan-mysql-data`, `hongvan-redis-data`, `hongvan-storage`.
