# P00 — Baseline repository

**Status:** DONE
**Date:** 2026-08-02
**Scope:** P00 only; no framework installation, no P01 execution, and no change to `Template/`, `FrontEndTemplate/`, or `SourceIntegrations/`.

## Repository

- Git repository: initialized (`main`, tracking `origin/main`).
- Baseline working tree: clean (`git status --short --branch` returned only `## main...origin/main`).
- Top-level inventory: `BackEnd/`, `Admin/`, `Template/`, `FrontEndTemplate/`, `SourceIntegrations/StayHubMedia/`, `prompts/`, and `docs/` all exist.
- `BackEnd/` has no root `artisan` or `composer.json`; `Admin/` has no root `package.json` or `angular.json`. Neither application is bootstrapped.

## Environment

| Item | Actual baseline |
|---|---|
| OS / shell | Windows 11 Pro 10.0.26200 (build 26200) / PowerShell 5.1.26100.8875 |
| PHP | 8.4.1 |
| Composer | 2.10.2 |
| Node.js / npm | v24.15.0 / 11.12.1 |
| Git | 2.44.0.windows.1 |
| MySQL client | Not available on `PATH` |
| Docker | 29.6.2 |

## Source gates

| Gate | Status | Evidence |
|---|---|---|
| Admin template | READY | `Template/` contains `angular.json`, `package.json`, and `package-lock.json`; its package manifest reports `@angular/core` 22.0.2. |
| Public frontend template | MISSING | `FrontEndTemplate/` contains the supplied placement README, which states that the source template must be copied there. |
| StayHub Media reference | MISSING | `SourceIntegrations/StayHubMedia/` contains the supplied placement README, which states the source has not yet been supplied. |

## Scope confirmed

The project is for CÔNG TY TNHH DV VT Hồng Vân: fertilizer catalog, transportation, warehousing, CMS/Page Builder, and lead intake. It is not an e-commerce project; cart, checkout, and payment remain out of scope.

## Blockers and risks

- PHP is 8.4.1 while `docs/TECH_STACK_LOCK.md` requires PHP 8.5.x before backend bootstrap (P04).
- The available Admin template uses Angular 22.0.2 while the lock targets Angular 22.1.x; preserve it unchanged and assess compatibility in the appropriate later checkpoint.
- The public frontend template and StayHub Media reference source are not supplied. Their dependent prompts must remain deferred until the corresponding source is present.

## Validation

- Documentation-only scope: no formatter, linter, backend test, admin test, or build runner applies because Laravel and Angular are not scaffolded.
- `git diff --cached --check` passed after the final state, ledger, and report staging.

## Proposed next prompt

`P01 — External source inventory`. This report does not execute P01.
