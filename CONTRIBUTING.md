# Contributing to Hong Van

Thank you for your interest in contributing to Hong Van.

Hong Van is an open-source company website and content management platform built with Laravel, Angular, Docker, MySQL, and Redis.

Contributions that improve reliability, security, documentation, accessibility, maintainability, testing, and supported project features are welcome.

## Before You Start

Please review:

* `README.md`
* `AGENTS.md`
* the nearest `AGENTS.md` file in the area you plan to change
* relevant documentation under `docs/`

Repository-level rules in `AGENTS.md` apply to the entire project unless a more specific rule is defined in a child directory.

## Project Scope

The current project includes:

* fertilizer product presentation
* company content management
* transportation services
* warehousing and storage services
* quotation and contact requests
* multilingual content
* SEO-oriented public pages
* Laravel Blade server-side rendering
* Angular administration
* Page Builder functionality
* media management
* permissions, auditing, and security controls

The following are currently outside the intended product scope:

* shopping carts
* checkout
* online payment
* e-commerce order processing

Please open a discussion or issue before implementing large functionality outside the established scope.

## Development Model

GitLab is the development source of truth for the project.

GitHub is maintained as the public open-source mirror.

Project maintainers integrate code through reviewed branches and Merge Requests on the development repository before changes are mirrored to GitHub.

For public contributions, start by opening an issue describing the proposed change. This allows the maintainers to coordinate the appropriate integration path before substantial work begins.

## Reporting Bugs

Before opening a bug report:

1. Check whether the issue has already been reported.
2. Confirm that it can be reproduced on the latest `main`.
3. Collect the smallest useful reproduction.
4. Remove credentials, tokens, personal information, and production data.

A useful bug report should include:

* a concise title
* affected component
* expected behavior
* actual behavior
* reproduction steps
* relevant logs or screenshots
* Docker and operating-system information when relevant

Do not include secrets or sensitive production information.

## Proposing Features

For substantial features, open an issue before writing the implementation.

Describe:

* the problem being solved
* the intended users
* the proposed behavior
* compatibility or migration concerns
* security implications
* alternatives considered

Large features should fit the existing project scope and architecture.

## Local Development

The standard local environment uses Docker Compose.

From the repository root, prepare the ignored environment files:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-docker-env.ps1
```

Make sure the shared Docker network exists:

```powershell
docker network inspect local-infra
```

If necessary:

```powershell
docker network create local-infra
```

Validate the Compose configuration:

```powershell
docker compose config --quiet
```

Build the images:

```powershell
docker compose build
```

Start MySQL and Redis:

```powershell
docker compose up -d mysql redis
```

Initialize the database:

```powershell
docker compose run --rm app php artisan migrate --force --seed
```

Start the complete stack:

```powershell
docker compose up -d
```

Check the environment:

```powershell
docker compose ps
```

See `README.md` for additional Docker commands and local-domain configuration.

## Branches

Keep each contribution focused on one logical change.

Use descriptive branch names such as:

```text
feat/product-filtering
fix/page-preview-expiry
docs/contributing-guide
test/public-search
refactor/media-service
```

Avoid mixing unrelated refactors, formatting, documentation, and feature work into the same change.

## Commit Messages

Use short, descriptive commit messages.

Recommended prefixes include:

```text
feat:
fix:
docs:
test:
refactor:
chore:
ci:
security:
```

Examples:

```text
feat: add warehouse capacity filters
fix: prevent expired preview access
docs: clarify Docker setup
test: cover quotation validation
security: harden uploaded file validation
```

Existing project-task commits may also use the project's `Pxx` or task identifier conventions where appropriate.

## Code Style

### Laravel / PHP

Follow the existing project architecture:

* keep controllers thin
* use Form Requests for validation
* keep business logic in domain Actions or Services
* use policies, gates, and permissions for authorization
* avoid unbound raw SQL
* use queues for expensive background work
* keep public Blade rendering server-side where appropriate

Run Laravel Pint for formatting.

### Angular / TypeScript

Follow the existing Angular architecture:

* use standalone components
* keep TypeScript strict
* avoid `any` unless there is a documented reason
* use typed data-access services instead of HTTP calls directly from components
* prefer Signals for local state
* lazy-load by feature
* use translation keys for user-facing text
* provide translations for Vietnamese, English, and Chinese when adding new user-facing strings

Preserve the project's administration layout and theme conventions.

## Database Rules

All project-owned tables must use the prefix:

```text
hongvan_
```

Do not use a connection-level table prefix.

Database changes must:

* use explicit project table names
* include appropriate indexes and constraints
* provide valid rollback behavior
* preserve or add table comments
* preserve or add column comments
* store timestamps in UTC
* avoid floating-point storage for money

Do not create unprefixed temporary tables with the intention of fixing them later.

## Page Builder Rules

Page Builder contributions must preserve the project's security model:

* database content stores versioned JSON documents
* arbitrary PHP, Blade, or JavaScript execution from database content is prohibited
* block types must come from an allowlist
* server-side validation remains authoritative
* published page versions are immutable
* rich text must be sanitized
* arbitrary CSS must not be introduced without an explicit secure design

## Product Pricing Rules

The project is not an e-commerce checkout platform.

Supported price behavior includes fixed, starting-from, range, market, dealer, quantity-based, and contact pricing.

Never render a missing price as:

```text
0đ
```

Products without a valid public price should use the contact or quotation flow.

## Security

Never commit:

* passwords
* access tokens
* API keys
* cookies or session identifiers
* private keys
* production `.env` files
* customer or employee personal data
* production database dumps

Do not disable CSRF, CORS, authorization, rate limiting, or other security controls simply to make a feature work.

Potential security vulnerabilities should not be disclosed publicly with exploitation details.

A dedicated `SECURITY.md` reporting policy will provide the private reporting process.

## Reference Sources

The following directories are reserved for reference material:

```text
Template/
FrontEndTemplate/
SourceIntegrations/
```

Do not commit proprietary, licensed, purchased, or externally sourced application code into these locations.

The CI pipeline enforces the public-repository reference-source policy.

## Tests and Quality Checks

Contributions should include tests appropriate to their scope.

The GitLab CI pipeline currently validates:

### Security

* Gitleaks
* reference-source policy
* Composer dependency advisories
* npm dependency advisories

### Backend

* Composer validation
* Composer audit
* dependency installation
* migrations
* database table-prefix checks
* Laravel Pint
* PHPStan
* PHPUnit

### Administration

* dependency installation
* npm audit
* linting
* unit tests
* production build
* Laravel asset synchronization

Playwright end-to-end tests are available for selected workflows.

A contribution is not considered ready when required CI checks are failing.

## Documentation

Update documentation when a change affects:

* installation
* configuration
* environment variables
* Docker services
* API behavior
* database conventions
* public behavior
* administrator workflows
* security assumptions

Documentation-only improvements are welcome.

## Keep Changes Focused

Please avoid unrelated cleanup while implementing a contribution.

A useful contribution should be easy to review and should clearly explain:

* what changed
* why it changed
* how it was tested
* whether it introduces migrations, API changes, or user-interface changes

## License

By contributing to this repository, you agree that your contributions may be distributed under the project's MIT License.

See `LICENSE` for the full license text.
