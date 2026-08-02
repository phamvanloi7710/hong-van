# TECH STACK LOCK

Verified on: **2026-08-02**

## Backend

| Thành phần | Dòng phiên bản |
|---|---|
| PHP | 8.5.x |
| Laravel | 13.x |
| Composer | latest stable compatible |
| MySQL | 8.4 LTS |
| Redis | stable supported release |
| Web server | Nginx + PHP-FPM |
| Queue | Laravel Queue + Redis |
| Scheduler | Laravel Scheduler |
| Public rendering | Laravel Blade |
| Asset bundler | Vite supplied by Laravel |
| API auth | Laravel Sanctum cookie/session |
| Test | PHPUnit 12.x hoặc test runner do Laravel 13 scaffold tạo, không trộn tùy tiện |
| Formatter | Laravel Pint |
| Static analysis | Larastan/PHPStan tương thích Laravel 13 |

## Admin

| Thành phần | Dòng phiên bản |
|---|---|
| Angular | 22.1.x |
| Angular CLI | cùng minor với Angular core |
| Node.js | 24.x, tối thiểu 24.15.0 theo compatibility Angular 22 |
| TypeScript | 6.0.x theo compatibility Angular 22 |
| RxJS | dòng tương thích do Angular CLI tạo |
| UI | Template tại `Template/` |
| Drag/drop | Angular CDK nếu template tương thích |
| Unit test | runner do Angular 22 scaffold tạo |
| E2E | Playwright bản tương thích |

## Chính sách phiên bản

- Pin major/minor quan trọng.
- Cho phép patch update sau khi test.
- Không dùng `latest` không kiểm soát trong production lockfile.
- Không nâng major tự động.
- Trước khi bootstrap, xác nhận patch stable mới nhất từ nguồn chính thức.
- Commit lockfile.
- Ghi mọi thay đổi dòng phiên bản vào ADR.

## Nguồn kiểm chứng

- Angular releases: https://angular.dev/reference/releases
- Angular compatibility: https://angular.dev/reference/versions
- Angular tags: https://github.com/angular/angular/releases
- Laravel 13: https://laravel.com/docs/13.x/releases
- Laravel deployment requirements: https://laravel.com/docs/13.x/deployment
- PHP 8.5: https://www.php.net/releases/8.5/en.php
- MySQL 8.4 LTS: https://dev.mysql.com/downloads/mysql/8.4.html
