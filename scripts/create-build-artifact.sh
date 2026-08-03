#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd -- "${script_dir}/.." && pwd)"
artifact_parent="${repo_root}/.tmp/artifacts"
skip_install=0

while [[ "$#" -gt 0 ]]; do
    case "$1" in
        --skip-install) skip_install=1; shift ;;
        --output)
            [[ "$#" -ge 2 ]] || { printf 'Missing value for --output.\n' >&2; exit 2; }
            artifact_parent="$(cd -- "$(dirname -- "$2")" && pwd)/$(basename -- "$2")"
            shift 2
            ;;
        *) printf 'Usage: %s [--skip-install] [--output DIRECTORY]\n' "$0" >&2; exit 2 ;;
    esac
done

artifact_dir="${artifact_parent}/hongvan-web-assets"
[[ "$(basename -- "${artifact_dir}")" == 'hongvan-web-assets' ]] || {
    printf 'Refusing unsafe artifact target: %s\n' "${artifact_dir}" >&2
    exit 2
}

if [[ "${skip_install}" -eq 0 ]]; then
    (cd -- "${repo_root}/BackEnd" && npm ci)
    (cd -- "${repo_root}/Admin" && npm ci)
fi

(cd -- "${repo_root}/BackEnd" && npm run build)
(cd -- "${repo_root}/Admin" && npm run build:laravel)

public_build="${repo_root}/BackEnd/public/build"
admin_build="${repo_root}/BackEnd/public/admin/browser"
[[ -d "${public_build}" && -d "${admin_build}" ]] || {
    printf 'Required public/admin build output is missing.\n' >&2
    exit 1
}

mkdir -p -- "${artifact_parent}"
rm -rf -- "${artifact_dir}"
mkdir -p -- "${artifact_dir}/public/admin"
cp -R -- "${public_build}" "${artifact_dir}/public/build"
cp -R -- "${admin_build}" "${artifact_dir}/public/admin/browser"

(
    cd -- "${artifact_dir}"
    find public -type f -print0 \
        | LC_ALL=C sort -z \
        | xargs -0 sha256sum > SHA256SUMS.txt
)

checksum_count="$(wc -l < "${artifact_dir}/SHA256SUMS.txt" | tr -d '[:space:]')"
printf 'Build artifact created at %s with %s checksums.\n' "${artifact_dir}" "${checksum_count}"
