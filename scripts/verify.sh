#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
skip_install=0
skip_readonly_source_check=0
run_migrations=0
run_e2e=0
smoke_base_url=''

while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --skip-install) skip_install=1; shift ;;
        --skip-readonly-source-check) skip_readonly_source_check=1; shift ;;
        --run-migrations) run_migrations=1; shift ;;
        --run-e2e) run_e2e=1; shift ;;
        --smoke-base-url)
            [[ "$#" -ge 2 ]] || { printf 'Missing value for --smoke-base-url.\n' >&2; exit 2; }
            smoke_base_url="$2"; shift 2
            ;;
        *) printf 'Usage: %s [--skip-install] [--skip-readonly-source-check] [--run-migrations] [--run-e2e] [--smoke-base-url URL]\n' "$0" >&2; exit 2 ;;
    esac
done

bash "${script_dir}/verify-prerequisites.sh"
if [[ "${skip_readonly_source_check}" -eq 0 ]]; then
    bash "${script_dir}/verify-readonly-sources.sh"
fi

backend_args=()
[[ "${skip_install}" -eq 1 ]] && backend_args+=(--skip-install)
[[ "${run_migrations}" -eq 1 ]] && backend_args+=(--run-migrations)
bash "${script_dir}/qa-backend.sh" "${backend_args[@]}"

admin_args=()
[[ "${skip_install}" -eq 1 ]] && admin_args+=(--skip-install)
[[ "${run_e2e}" -eq 1 ]] && admin_args+=(--run-e2e)
bash "${script_dir}/qa-admin.sh" "${admin_args[@]}"

if [[ -n "${smoke_base_url}" ]]; then
    bash "${script_dir}/smoke.sh" "${smoke_base_url}"
fi

printf 'Repository verification completed successfully.\n'
