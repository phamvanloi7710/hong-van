# Hong Van

Open-source company website and content management platform for **CÃ”NG TY TNHH DV VT Há»’NG VÃ‚N**, built with Laravel, Angular, Docker, MySQL, and Redis.

[![OSS PR Policy](https://github.com/phamvanloi7710/hong-van/actions/workflows/oss-pr-policy.yml/badge.svg)](https://github.com/phamvanloi7710/hong-van/actions/workflows/oss-pr-policy.yml)
[![Latest release](https://img.shields.io/github/v/release/phamvanloi7710/hong-van?include_prereleases)](https://github.com/phamvanloi7710/hong-van/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

The project focuses on public company content, fertilizer product presentation, transportation services, warehousing, lead collection, quotation requests, multilingual content, SEO, and internal content administration.

> The platform is not designed as an e-commerce system. Products may display pricing when available or direct visitors to request a quotation.

## Features

### Public website

* Server-side rendered public website using Laravel Blade.
* Fertilizer product catalog.
* Product pricing or contact-for-quotation behavior.
* Transportation service presentation and request forms.
* Warehouse and storage service presentation and request forms.
* Contact and quotation request workflows.
* Multilingual content.
* SEO-oriented server rendering.
* Sitemap, metadata, and structured content foundations.
* Public-facing page composition through the Page Builder.

### Administration

* Angular-based administration application.
* Authentication and permission-aware administration.
* Product and content management.
* Media management foundation.
* Page Builder with controlled blocks and bindings.
* Preview workflow using the public rendering system.
* Per-user interface preferences.
* Theme and favorite-menu preferences.
* Localization management.
* Lead and request management.
* Dashboard and reporting foundations.
* Audit and security controls.

## Tech Stack

| Layer             | Technology                          |
| ----------------- | ----------------------------------- |
| Backend           | PHP 8.5, Laravel 13                 |
| Authentication    | Laravel Sanctum                     |
| Public frontend   | Laravel Blade SSR                   |
| Admin frontend    | Angular 22.1, Angular Material      |
| Language          | TypeScript 6                        |
| Database          | MySQL 8.4 LTS                       |
| Cache / Queue     | Redis                               |
| Web server        | Nginx                               |
| PHP runtime       | PHP-FPM                             |
| Containers        | Docker Compose                      |
| CI/CD             | GitLab CI, GitHub Actions           |
| Backend quality   | PHPUnit, PHPStan, Laravel Pint      |
| Admin quality     | Angular tests, ESLint, Playwright   |
| Security scanning | Composer Audit, npm Audit, Gitleaks |

## Architecture

```text
                         +--------------------+
                         |      Browser       |
                         +----------+---------+
                                    |
                                    v
                         +--------------------+
                         |       Nginx        |
                         +----------+---------+
                                    |
                    +---------------+---------------+
                    |                               |
                    v                               v
          +-------------------+           +-------------------+
          | Laravel Blade SSR |           |   Angular Admin   |
          +---------+---------+           +---------+---------+
                    |                               |
                    +---------------+---------------+
                                    |
                                    v
                         +--------------------+
                         |    Laravel API     |
                         +---------+----------+
                                   |
                    +--------------+--------------+
                    |                             |
                    v                             v
             +-------------+               +-------------+
             | MySQL 8.4   |               |    Redis    |
             +-------------+               +-------------+
```

The public website prioritizes server-side rendering and SEO, while the Angular application provides the administration interface.

## Repository Structure

```text
hong-van/
â”œâ”€â”€ Admin/                    Angular administration application
â”œâ”€â”€ BackEnd/                  Laravel API and public Blade application
â”œâ”€â”€ docker/                   PHP and Nginx container configuration
â”œâ”€â”€ docs/                     Project and implementation documentation
â”œâ”€â”€ prompts/                  Historical implementation prompt workflow
â”œâ”€â”€ scripts/                  Development, CI, and Docker utilities
â”œâ”€â”€ Template/                 Admin reference source location
â”œâ”€â”€ FrontEndTemplate/         Public frontend reference source location
â”œâ”€â”€ SourceIntegrations/       External integration reference locations
â”œâ”€â”€ compose.yaml              Local Docker Compose stack
â”œâ”€â”€ .gitlab-ci.yml            CI pipeline
â”œâ”€â”€ .github/workflows/        Public pull-request checks
â”œâ”€â”€ LICENSE                   MIT License
â””â”€â”€ README.md
```

### Reference source policy

`Template/`, `FrontEndTemplate/`, and `SourceIntegrations/` are reserved for reference sources.

Proprietary or external source packages must not be committed to the public repository. CI enforces this policy and only permits the repository-owned placeholder documentation in these locations.

## Database Convention

Project-owned database tables must use the prefix:

```text
hongvan_
```

The project does not rely on a connection-level table prefix. Table names are explicitly defined by the application.

## Requirements

For the current Docker-based local environment:

* Git
* Docker Desktop or Docker Engine
* Docker Compose
* PowerShell for the provided environment bootstrap script
* A Docker network named `local-infra`
* A shared reverse proxy connected to `local-infra` for local HTTP routing

PHP, Node.js, MySQL, Redis, Nginx, and application dependencies are provided through containers or Docker build stages for the standard local workflow.

## Quick Start with Docker

### 1. Prepare environment files

From the repository root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-docker-env.ps1
```

The script creates ignored environment files and generates local secrets when they do not already exist.

Never commit generated `.env` files or real credentials.

### 2. Check the shared Docker network

```powershell
docker network inspect local-infra
```

If the network does not exist:

```powershell
docker network create local-infra
```

### 3. Validate the Compose configuration

```powershell
docker compose config --quiet
```

### 4. Build the application images

```powershell
docker compose build
```

### 5. Start MySQL and Redis

```powershell
docker compose up -d mysql redis
```

### 6. Run migrations and seed the initial data

```powershell
docker compose run --rm app php artisan migrate --force --seed
```

### 7. Start the complete stack

```powershell
docker compose up -d
```

### 8. Check container status

```powershell
docker compose ps
```

## Local Domains

The current local environment expects:

```text
127.0.0.1 hongvan.local
127.0.0.1 hongvan-pma.local
```

Add these entries to your local hosts file.

When the shared reverse proxy is configured and attached to `local-infra`, the expected local endpoints are:

```text
Public website:
http://hongvan.local/

Administration:
http://hongvan.local/admin/

phpMyAdmin:
http://hongvan-pma.local/
```

The Compose stack intentionally does not publish a dedicated HTTP port for each application. Local HTTP routing is expected to be handled by the shared reverse proxy.

## Docker Services

The local stack contains the following project-specific services:

```text
mysql
redis
phpmyadmin
app
queue
scheduler
nginx
```

Persistent application data is stored in named Docker volumes for:

```text
hongvan-mysql-data
hongvan-redis-data
hongvan-storage
```

Running:

```powershell
docker compose down
```

stops the stack while preserving these named volumes.

Do not use:

```powershell
docker compose down -v
```

unless you intentionally want to delete the project database, Redis data, and persisted storage.

## Useful Docker Commands

Check running services:

```powershell
docker compose ps
```

View application logs:

```powershell
docker compose logs -f app
```

View Nginx logs:

```powershell
docker compose logs -f nginx
```

Check migration status:

```powershell
docker compose exec app php artisan migrate:status
```

Clear Laravel caches:

```powershell
docker compose exec app php artisan optimize:clear
```

Check Redis:

```powershell
docker compose exec redis sh -c 'REDISCLI_AUTH="$REDIS_PASSWORD" redis-cli ping'
```

Stop the environment:

```powershell
docker compose down
```

## Development Notes

The current application image is built from repository source instead of bind-mounting the complete working tree into the running container.

After source changes that affect the built application, rebuild the relevant images before validating the running stack.

For example:

```powershell
docker compose build app nginx
docker compose up -d --force-recreate app queue scheduler nginx
```

The production-style `app` image is installed without Composer development dependencies. PHPUnit and other development-only tools are therefore not expected to be available in the normal running `app` container.

The authoritative full quality suite currently runs in GitLab CI.

Public GitHub pull requests also run read-only, secret-safe policy checks. Backend and Admin quality workflows run only when the corresponding paths change.

## Testing and Quality

Every merge request is expected to pass the project CI pipeline.

### Security checks

CI validates:

* Git history with Gitleaks.
* Reference-source commit policy.
* Composer security advisories.
* npm production dependency advisories.
* Critical npm dependency advisories.

### Backend checks

CI runs:

```text
composer validate
composer audit
composer install
Laravel migrations
table-prefix validation
Laravel Pint
PHPStan
PHPUnit
```

### Admin checks

CI runs:

```text
npm ci
npm audit
lint
unit tests
production build
Laravel asset synchronization
```

Playwright end-to-end tests are available as an optional CI stage.

## Project Invariants

The following rules are intentionally enforced throughout the project:

* Project-owned database tables use the `hongvan_` prefix.
* Secrets and generated `.env` files must never be committed.
* Proprietary reference source must not be committed.
* Database content must not execute arbitrary Blade, PHP, or JavaScript.
* Missing product prices must not be rendered as `0`.
* Products without an appropriate public price should use the quotation/contact flow.
* Cart, checkout, payment, and order workflows are outside the current product scope.
* Public rendering should remain SEO-friendly and server-side where appropriate.
* Administrative functionality must remain permission-aware.

## Repository Workflow

GitLab is the development source of truth.

GitHub is maintained as the public mirror of the project.

Code changes should follow the GitLab branch and Merge Request workflow, pass the required CI pipeline, and then be mirrored to GitHub after merge.

Public contributors can start from [CONTRIBUTING.md](CONTRIBUTING.md), open a GitHub Issue, and submit a GitHub Pull Request. Maintainers reconcile accepted contributions through the authoritative GitLab workflow.

## Security

Security is treated as part of the development lifecycle through dependency auditing, secret scanning, permission checks, static analysis, and automated tests.

Use the private reporting process in [SECURITY.md](SECURITY.md). Never include secrets, credentials, private production data, or sensitive vulnerability details in a public Issue.

## Roadmap

The public roadmap, release themes, and contribution-ready work are maintained in [ROADMAP.md](ROADMAP.md) and GitHub Issues.

## Versioning and releases

Public releases follow [Semantic Versioning](https://semver.org/). The project is currently in the `0.x` series, so breaking changes may still occur between minor releases and will be documented.

Release notes are maintained in [CHANGELOG.md](CHANGELOG.md). `CHANGELOG_V2.md` remains the historical changelog for the original implementation prompt pack.

## Documentation

Additional project documentation is available in:

```text
START_HERE.md
HUONG_DAN_TRIEN_KHAI_TU_DAU.md
DANH_SACH_PROMPT_CHI_TIET.md
docs/
prompts/
```

The prompt documents describe the historical implementation sequence and remain useful as architectural and project-history references.

## License

This project is licensed under the MIT License.

See:

```text
LICENSE
```

for the full license text.

