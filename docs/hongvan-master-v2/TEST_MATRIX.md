# TEST MATRIX

## Fast gate theo task

- Backend domain: PHPUnit filter + Pint + PHPStan scope.
- Migration: migrate fresh/rollback + TablePrefixTest + DatabaseCommentTest.
- Angular: lint + relevant Vitest + `npm run build:laravel`.
- Public Blade: relevant feature tests + Vite build.
- E2E: Playwright spec đúng workflow.

## Full gates

- T224: toàn Backend.
- T225: toàn Angular/Playwright/visual.
- T226: accessibility/performance.
- T227: dependency/license/secret/container scans.
- T228: CI reproducibility.
- T235–T240: full release recertification.

Không được bỏ qua test hoặc nới budget chỉ để pass.
