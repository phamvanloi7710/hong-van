#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd -- "${script_dir}/.." && pwd)"
backend_dir="${repo_root}/BackEnd"
skip_install=0
run_migrations=0

for argument in "$@"; do
    case "${argument}" in
        --skip-install) skip_install=1 ;;
        --run-migrations) run_migrations=1 ;;
        *) printf 'Usage: %s [--skip-install] [--run-migrations]\n' "$0" >&2; exit 2 ;;
    esac
done

[[ -f "${backend_dir}/composer.lock" ]] || {
    printf 'BackEnd/composer.lock is required for reproducible backend QA.\n' >&2
    exit 2
}

cd -- "${backend_dir}"
composer validate --strict --no-check-publish
composer audit --locked

if [[ "${skip_install}" -eq 0 ]]; then
    composer install --no-interaction --prefer-dist --no-progress
fi

[[ -f vendor/autoload.php ]] || {
    printf 'Backend dependencies are missing. Run without --skip-install.\n' >&2
    exit 2
}

if [[ "${run_migrations}" -eq 1 ]]; then
    if [[ "${APP_ENV:-}" != 'testing' && "${APP_ENV:-}" != 'ci' ]] \
        || [[ ! "${DB_DATABASE:-}" =~ (_testing|_ci)$ ]]; then
        printf 'Migration QA requires APP_ENV=testing|ci and DB_DATABASE ending in _testing or _ci.\n' >&2
        exit 2
    fi
    php artisan migrate --force
fi

php "${repo_root}/scripts/check-table-prefix.php"
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --memory-limit=1G
php artisan test
printf 'Backend QA completed successfully.\n'
