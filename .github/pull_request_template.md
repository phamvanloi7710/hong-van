# Summary

<!--
Briefly describe what this change does and why it is needed.
Keep the scope focused on one logical change.
-->

## Related Issue

<!--
Link the related issue when applicable.

Examples:
Closes #123
Fixes #123
Related to #123
-->

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Refactor
- [ ] Documentation
- [ ] Tests
- [ ] CI / build
- [ ] Security
- [ ] Dependency update
- [ ] Other

## Affected Areas

- [ ] Public website
- [ ] Admin application
- [ ] Laravel API
- [ ] Authentication / permissions
- [ ] Page Builder
- [ ] Media management
- [ ] Products / pricing
- [ ] SEO
- [ ] Transportation
- [ ] Warehouses
- [ ] Dashboard / reports
- [ ] Localization
- [ ] Database / migrations
- [ ] Docker / local environment
- [ ] Queue / scheduler / Redis
- [ ] CI / quality tooling
- [ ] Documentation
- [ ] Other

## What Changed

<!--
Describe the implementation at a reviewable level.

Mention important files, components, services, endpoints,
database structures, or architectural decisions.
-->

## Why

<!--
Explain the problem being solved or the reason for the change.
-->

## Database Changes

- [ ] No database changes
- [ ] Migration added
- [ ] Migration modified
- [ ] Seeder / factory changed
- [ ] Database indexes or constraints changed
- [ ] Database comments changed

### Database Notes

<!--
If applicable, describe:
- tables affected
- migration behavior
- rollback behavior
- indexes / foreign keys
- data migration concerns
-->

## API Changes

- [ ] No API changes
- [ ] New endpoint
- [ ] Existing endpoint changed
- [ ] Request contract changed
- [ ] Response contract changed
- [ ] Authorization behavior changed
- [ ] Rate-limit behavior changed

### API Notes

<!--
Describe compatibility impact when applicable.
-->

## UI Changes

- [ ] No UI changes
- [ ] Public website changed
- [ ] Admin UI changed
- [ ] Responsive behavior changed
- [ ] Accessibility behavior changed
- [ ] User-facing translations changed

### Screenshots

<!--
For visible UI changes, add before/after screenshots or recordings when useful.
Remove this section if not applicable.
-->

## Security Impact

- [ ] No security impact identified
- [ ] Authentication changed
- [ ] Authorization / permissions changed
- [ ] CSRF / session behavior changed
- [ ] Input validation changed
- [ ] File upload behavior changed
- [ ] Page Builder / rich content behavior changed
- [ ] Secrets / configuration handling changed
- [ ] Logging / audit behavior changed
- [ ] Dependency security update
- [ ] Other security-sensitive behavior changed

### Security Notes

<!--
Describe relevant threats, mitigations, or validation performed.

Do not include secrets, credentials, tokens, production data,
or exploitable vulnerability details that should remain private.
-->

## Testing

<!--
List the exact tests or checks you ran.

Examples:

docker compose config --quiet
composer audit --locked
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --memory-limit=1G
php artisan test
npm run lint
npm test -- --watch=false
npm run build:laravel

Also include whether each command passed or failed.
-->

Test commands and results:

<!--
Example:

- docker compose config --quiet : PASS
- composer audit --locked : PASS
- php artisan test : PASS
-->

## Manual Verification

<!--
Describe important manual checks, if any.

Examples:
- verified public page in Chrome
- verified admin workflow
- verified migration and rollback
- verified Docker service health
-->

## Deployment / Configuration Impact

- [ ] No deployment changes
- [ ] Environment variable added or changed
- [ ] Docker configuration changed
- [ ] Queue / scheduler behavior changed
- [ ] Cache / Redis behavior changed
- [ ] Web server configuration changed
- [ ] Deployment instructions changed

### Deployment Notes

<!--
Describe any required operator action.
-->

## Documentation

- [ ] No documentation update required
- [ ] README updated
- [ ] CONTRIBUTING updated
- [ ] SECURITY updated
- [ ] Other documentation updated
- [ ] Documentation follow-up is required

## Final Checklist

- [ ] I reviewed the relevant `AGENTS.md` rules.
- [ ] This change is focused and does not include unrelated refactoring.
- [ ] Project-owned database tables use the `hongvan_` prefix.
- [ ] No secrets, credentials, private keys, production `.env` files, or sensitive production data are included.
- [ ] Proprietary or licensed reference source has not been committed.
- [ ] Required tests, linting, static analysis, and builds have been run or CI will run them.
- [ ] New user-facing text includes the required translations where applicable.
- [ ] Missing product prices are not rendered as `0đ`.
- [ ] Cart, checkout, payment, or order workflows were not introduced outside approved project scope.
- [ ] Security-sensitive changes preserve existing protections rather than disabling them.
- [ ] Documentation was updated when behavior or setup changed.

## Reviewer Notes

<!--
Call out anything reviewers should inspect especially closely.
-->