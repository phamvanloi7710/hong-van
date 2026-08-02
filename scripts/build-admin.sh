#!/usr/bin/env bash
set -euo pipefail

script_directory="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repository_directory="$(cd -- "${script_directory}/.." && pwd)"
admin_directory="${repository_directory}/Admin"
mode="full"
skip_install="false"

for argument in "$@"; do
    case "${argument}" in
        --build-only)
            mode="build-only"
            ;;
        --skip-install)
            skip_install="true"
            ;;
        *)
            echo "Unknown argument: ${argument}" >&2
            echo "Usage: $0 [--build-only] [--skip-install]" >&2
            exit 2
            ;;
    esac
done

cd "${admin_directory}"

if [[ "${skip_install}" == "false" ]] && {
    [[ ! -f node_modules/.package-lock.json ]] ||
        [[ package-lock.json -nt node_modules/.package-lock.json ]]
}; then
    npm ci
fi

if [[ "${mode}" == "full" ]]; then
    npm run lint
    npm test -- --watch=false
fi

npm run build:laravel
