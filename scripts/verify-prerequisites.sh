#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd -- "${script_dir}/.." && pwd)"

if [[ ! -f "${repo_root}/AGENTS.md" ]]; then
    printf 'Repository root could not be verified from: %s\n' "${script_dir}" >&2
    exit 2
fi

has_failure=0

extract_version() {
    local command_name="$1"
    shift

    if ! command -v "${command_name}" >/dev/null 2>&1; then
        return 1
    fi

    "${command_name}" "$@" 2>&1 \
        | grep -Eo '[0-9]+\.[0-9]+(\.[0-9]+)?' \
        | head -n 1
}

version_at_least() {
    local actual="$1"
    local minimum="$2"
    [[ "$(printf '%s\n%s\n' "${minimum}" "${actual}" | sort -V | head -n 1)" == "${minimum}" ]]
}

report_result() {
    local tool="$1"
    local version="$2"
    local compatible="$3"
    local expected="$4"

    if [[ -z "${version}" ]]; then
        printf '[MISSING]      %-10s expected %s\n' "${tool}" "${expected}"
        has_failure=1
    elif [[ "${compatible}" == '1' ]]; then
        printf '[OK]           %-10s %s\n' "${tool}" "${version}"
    else
        printf '[INCOMPATIBLE] %-10s %s; expected %s\n' "${tool}" "${version}" "${expected}"
        has_failure=1
    fi
}

php_version="$(extract_version php --version || true)"
composer_version="$(extract_version composer --version || true)"
node_version="$(extract_version node --version || true)"
npm_version="$(extract_version npm --version || true)"
git_version="$(extract_version git --version || true)"

php_ok=0
[[ "${php_version}" == 8.5.* ]] && php_ok=1
composer_ok=0
[[ "${composer_version}" == 2.* ]] && composer_ok=1
node_ok=0
if [[ -n "${node_version}" ]] \
    && version_at_least "${node_version}" '24.15.0' \
    && ! version_at_least "${node_version}" '25.0.0'; then
    node_ok=1
fi
npm_ok=0
[[ -n "${npm_version}" ]] && npm_ok=1
git_ok=0
[[ "${git_version}" == 2.* || "${git_version}" == 3.* ]] && git_ok=1

report_result 'PHP' "${php_version}" "${php_ok}" '8.5.x'
report_result 'Composer' "${composer_version}" "${composer_ok}" '2.x'
report_result 'Node.js' "${node_version}" "${node_ok}" '>= 24.15.0 and < 25.0.0'
report_result 'npm' "${npm_version}" "${npm_ok}" 'available with the target Node.js runtime'
report_result 'Git' "${git_version}" "${git_ok}" '2.x or newer'

if [[ "${has_failure}" -ne 0 ]]; then
    printf 'Prerequisite verification completed with missing or incompatible tools.\n'
    exit 1
fi

printf 'All prerequisite checks passed.\n'
