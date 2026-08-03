#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd -- "${script_dir}/.." && pwd)"
admin_dir="${repo_root}/Admin"
skip_install=0
run_e2e=0

for argument in "$@"; do
    case "${argument}" in
        --skip-install) skip_install=1 ;;
        --run-e2e) run_e2e=1 ;;
        *) printf 'Usage: %s [--skip-install] [--run-e2e]\n' "$0" >&2; exit 2 ;;
    esac
done

[[ -f "${admin_dir}/package-lock.json" ]] || {
    printf 'Admin/package-lock.json is required for reproducible admin QA.\n' >&2
    exit 2
}

cd -- "${admin_dir}"
if [[ "${skip_install}" -eq 0 ]]; then
    npm ci
fi
[[ -f node_modules/.package-lock.json ]] || {
    printf 'Admin dependencies are missing. Run without --skip-install.\n' >&2
    exit 2
}

npm audit --omit=dev --audit-level=high
npm audit --audit-level=critical
npm run lint
npm test -- --watch=false
npm run build:laravel
if [[ "${run_e2e}" -eq 1 ]]; then
    npx playwright test
fi
printf 'Admin QA completed successfully.\n'
