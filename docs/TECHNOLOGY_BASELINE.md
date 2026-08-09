# Technology Baseline

Baseline được đối chiếu tại base HEAD `bf70acc401e674e8856255e04031e2b491a2b124` ngày 2026-08-09. Runtime local hợp lệ là Docker Desktop; không dùng runtime WAMP để nghiệm thu.

## Phiên bản đã xác minh

| Thành phần | Contract/source tại HEAD | Patch version thực tế | Kết quả |
|---|---|---|---|
| PHP | `^8.5`; CI `php:8.5-cli-bookworm` | Docker app `8.5.0` | PASS |
| Laravel | `^13.0` | `13.23.0` | PASS |
| Laravel Sanctum | `^4.3` | `4.3.3` | PASS |
| Composer | Docker image copy `composer:2.8.10` | `2.8.10` | PASS, có cảnh báo deprecation trên PHP 8.5 |
| Node.js | Admin engine `^22.22.3 || ^24.15.0 || >=26.0.0`; CI `24.15.0` | `24.15.0` | PASS |
| npm | lock/runtime Admin | `11.12.1` | PASS |
| Angular | package constraint `~22.1.0` | Core/Material/CDK `22.1.0` | PASS |
| Angular CLI/build | package constraint `~22.1.2` | CLI/build `22.1.2` | PASS |
| TypeScript | `~6.0.2` | `6.0.3` | PASS |
| RxJS | `~7.8.0` | `7.8.2` | PASS |
| Vite | package lock | `8.1.5` | PASS |
| Vitest | `^4.0.8` | `4.1.10` | PASS |
| MySQL | CI `mysql:8.4` | Docker local `8.4.6` | PASS |
| Redis server | CI `redis:7.4-alpine` | Docker local `7.4.5` | PASS |
| PHP Redis extension | Composer platform/runtime | `6.3.0` | PASS |
| Docker Desktop / Engine | local runtime | Desktop `4.85.0`, Engine `29.6.2` | PASS |

## Kiểm tra tương thích

- Angular 22.1.0 chạy với Node 24.15.0, TypeScript 6.0.3 và RxJS 7.8.2, phù hợp ma trận tương thích Angular 22 tại <https://angular.dev/reference/versions>.
- `composer check-platform-reqs --no-dev` trong container app xác nhận PHP 8.5.0 và toàn bộ extension production đều đạt.
- Stack Docker đang healthy: `app`, `nginx`, `queue`, `scheduler`, `local-mysql` và `local-redis`.
- Không nâng major hoặc thay đổi dependency trong T007.

## Incompatibility và rủi ro còn lại

- Composer 2.8.10 phát cảnh báo deprecation từ dependency nội bộ khi chạy trên PHP 8.5.0. Không chặn cài đặt/platform check, nhưng cần theo dõi khi nâng Composer ở task chuyên trách.
- Các tag CI `php:8.5-cli-bookworm`, `mysql:8.4` và `redis:7.4-alpine` không khóa patch/digest; patch CI có thể trôi theo registry. Runtime Docker local hiện đã cho patch version cụ thể ở bảng trên.
- Cấu hình Docker local đang thuộc thay đổi P51 chưa commit tại thời điểm audit; T007 chỉ đọc runtime, không stage hoặc sửa các file P51.

## Bằng chứng runtime

```text
docker version
docker compose ps
docker compose -f docker/infrastructure/compose.yaml ps
docker compose exec -T app php -v
docker compose exec -T app php artisan --version
docker compose exec -T app composer --version
docker compose exec -T app composer check-platform-reqs --no-dev
docker compose -f docker/infrastructure/compose.yaml exec -T mysql mysql --version
docker compose -f docker/infrastructure/compose.yaml exec -T redis redis-server --version
docker compose exec -T app php -r "echo phpversion('redis'), PHP_EOL;"
node --version
npm --version
npx ng version
```
