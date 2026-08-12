# Hong Van Roadmap

This roadmap describes public open-source priorities. It is directional rather than a promise of delivery dates. GitHub Issues hold the reviewable scope and acceptance criteria for individual contributions.

## Released foundation

### v0.1.0 â€” Open-source baseline

- MIT license and contributor documentation.
- Private vulnerability reporting policy.
- Structured bug, feature, and documentation issue forms.
- Pull Request template.
- Reproducible GitLab CI backed by a self-hosted runner.
- Read-only GitHub Actions checks for public Pull Requests.
- Laravel 13 backend, Angular 22 Admin, public Blade rendering, MySQL, Redis, and Docker foundations.

## Near term â€” v0.2.x

### Contributor experience

- [#1](https://github.com/phamvanloi7710/hong-van/issues/1) â€” Provide a one-command contributor health check for Docker, database, Redis, backend, and Admin prerequisites.
- [#2](https://github.com/phamvanloi7710/hong-van/issues/2) â€” Reduce GitHub backend CI startup time with a reproducible prebuilt quality image or equivalent cache-safe approach.
- [#5](https://github.com/phamvanloi7710/hong-van/issues/5) â€” Document the public-contribution path from GitHub Pull Request to the authoritative GitLab merge.

### Public-site confidence

- [#3](https://github.com/phamvanloi7710/hong-van/issues/3) â€” Add a focused Playwright smoke suite for public contact and quotation journeys.
- [#4](https://github.com/phamvanloi7710/hong-van/issues/4) â€” Complete dynamic public routing for published Page Builder pages.
- [#6](https://github.com/phamvanloi7710/hong-van/issues/6) â€” Publish clearly licensed sample media so a fresh local environment can demonstrate the product without proprietary source packages.

## Medium term â€” v0.3.x

- Expand accessibility and performance budgets for public and Admin surfaces.
- Strengthen multilingual content review across Vietnamese, English, and Chinese.
- Improve operational dashboards for queues, scheduled publishing, leads, and audit activity.
- Add upgrade and rollback evidence to the production deployment runbook.

## Long term â€” 1.0 readiness

Version 1.0 requires:

- a documented stable public contract and upgrade policy;
- production deployment evidence and recovery drills;
- repeatable security and dependency maintenance;
- maintained contributor onboarding and issue triage;
- no known release-blocking defects in core content, product, lead, media, SEO, and Page Builder workflows.

## Explicit non-goals

- Shopping cart, checkout, payment, and e-commerce order processing.
- Executing arbitrary PHP, Blade, JavaScript, or unrestricted CSS from database content.
- Committing proprietary template or integration source packages.

## Contributing

Choose an open issue labelled `good first issue` or `help wanted`, or open a proposal before starting a large change. See [CONTRIBUTING.md](CONTRIBUTING.md).

