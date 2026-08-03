#!/usr/bin/env bash
set -euo pipefail

base_url="${1:-http://hongvan.local}"
base_url="${base_url%/}"

case "${base_url}" in
    http://*|https://*) ;;
    *) printf 'Base URL must use HTTP or HTTPS.\n' >&2; exit 2 ;;
esac

check_status() {
    local path="$1"
    local expected="$2"
    local url="${base_url}/${path}"
    local actual
    actual="$(curl --silent --show-error --output /dev/null --max-time 15 --user-agent 'HongVan-CI-Smoke/1.0' --write-out '%{http_code}' "${url}")"
    if [[ "${actual}" != "${expected}" ]]; then
        printf 'Smoke check failed for %s: expected %s, received %s.\n' "${url}" "${expected}" "${actual}" >&2
        exit 1
    fi
    printf '[OK] %s -> %s\n' "${url}" "${actual}"
}

check_status 'health' '200'
check_status 'api/public/v1/system/ping' '200'
check_status 'admin/' '200'
check_status 'api/admin/v1/auth/me' '401'
printf 'HTTP smoke checks completed successfully.\n'
