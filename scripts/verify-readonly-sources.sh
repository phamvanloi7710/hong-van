#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd -- "${script_dir}/.." && pwd)"
baseline_path="${repo_root}/.readonly-sources.sha256"
source_paths=('Template' 'FrontEndTemplate' 'SourceIntegrations')
print_baseline=0

if [[ "${1:-}" == '--print-baseline' ]]; then
    print_baseline=1
elif [[ "$#" -gt 0 ]]; then
    printf 'Usage: %s [--print-baseline]\n' "$0" >&2
    exit 2
fi

if [[ ! -f "${repo_root}/AGENTS.md" ]]; then
    printf 'Repository root could not be verified from: %s\n' "${script_dir}" >&2
    exit 2
fi

if ! command -v git >/dev/null 2>&1; then
    printf 'Git is required to calculate read-only source fingerprints.\n' >&2
    exit 2
fi

declare -A actual_hashes
declare -A actual_counts

fingerprint_directory() {
    local relative_directory="$1"
    local absolute_directory="${repo_root}/${relative_directory}"
    local relative_file
    local paths_file
    local git_paths_file
    local hashes_file
    local manifest_file
    local count

    if [[ ! -d "${absolute_directory}" ]]; then
        return 1
    fi

    paths_file="$(mktemp)"
    git_paths_file="$(mktemp)"
    hashes_file="$(mktemp)"
    manifest_file="$(mktemp)"

    while IFS= read -r -d '' relative_file; do
        relative_file="${relative_file#./}"
        if [[ "${relative_file}" == *$'\n'* || "${relative_file}" == *$'\r'* ]]; then
            rm -f -- "${paths_file}" "${git_paths_file}" "${hashes_file}" "${manifest_file}"
            printf 'Unsupported newline in path under %s.\n' "${relative_directory}" >&2
            return 2
        fi
        printf '%s\n' "${relative_file}" >> "${paths_file}"
        printf '%s/%s\n' "${relative_directory}" "${relative_file}" >> "${git_paths_file}"
    done < <(cd -- "${absolute_directory}" && find . -type f -print0 | LC_ALL=C sort -z)

    count="$(wc -l < "${paths_file}" | tr -d '[:space:]')"
    if ! (cd -- "${repo_root}" && git hash-object --stdin-paths < "${git_paths_file}" > "${hashes_file}"); then
        rm -f -- "${paths_file}" "${git_paths_file}" "${hashes_file}" "${manifest_file}"
        return 2
    fi
    if [[ "$(wc -l < "${hashes_file}" | tr -d '[:space:]')" != "${count}" ]]; then
        rm -f -- "${paths_file}" "${git_paths_file}" "${hashes_file}" "${manifest_file}"
        return 2
    fi

    paste -d ' ' "${hashes_file}" "${paths_file}" > "${manifest_file}"
    actual_hashes["${relative_directory}"]="$(sha256sum -- "${manifest_file}" | awk '{print tolower($1)}')"
    actual_counts["${relative_directory}"]="${count}"
    rm -f -- "${paths_file}" "${git_paths_file}" "${hashes_file}" "${manifest_file}"
}

for source_path in "${source_paths[@]}"; do
    if ! fingerprint_directory "${source_path}"; then
        actual_hashes["${source_path}"]=''
        actual_counts["${source_path}"]='0'
    fi
done

if [[ "${print_baseline}" -eq 1 ]]; then
    for source_path in "${source_paths[@]}"; do
        if [[ -z "${actual_hashes[${source_path}]}" ]]; then
            printf 'Required read-only source directory is missing: %s\n' "${source_path}" >&2
            exit 1
        fi
        printf '%s  %s\n' "${actual_hashes[${source_path}]}" "${source_path}"
    done
    exit 0
fi

if [[ ! -f "${baseline_path}" ]]; then
    printf '[MISSING] Baseline file .readonly-sources.sha256 was not found.\n'
    printf 'Run with --print-baseline, review the output, and update the baseline intentionally.\n'
    exit 1
fi

declare -A expected_hashes
while IFS= read -r baseline_line || [[ -n "${baseline_line}" ]]; do
    [[ -z "${baseline_line}" || "${baseline_line}" == \#* ]] && continue
    if [[ "${baseline_line}" =~ ^([0-9a-fA-F]{64})[[:space:]][[:space:]](.+)$ ]]; then
        expected_hashes["${BASH_REMATCH[2]}"]="${BASH_REMATCH[1],,}"
    else
        printf 'Invalid baseline entry: %s\n' "${baseline_line}" >&2
        exit 2
    fi
done < "${baseline_path}"

has_failure=0
for source_path in "${source_paths[@]}"; do
    if [[ -z "${actual_hashes[${source_path}]}" ]]; then
        printf '[MISSING] %s\n' "${source_path}"
        has_failure=1
    elif [[ -z "${expected_hashes[${source_path}]:-}" ]]; then
        printf '[UNTRACKED] %s (%s files)\n' "${source_path}" "${actual_counts[${source_path}]}"
        has_failure=1
    elif [[ "${expected_hashes[${source_path}]}" != "${actual_hashes[${source_path}]}" ]]; then
        printf '[CHANGED] %s (%s files)\n' "${source_path}" "${actual_counts[${source_path}]}"
        has_failure=1
    else
        printf '[MATCH]   %s (%s files)\n' "${source_path}" "${actual_counts[${source_path}]}"
    fi
done

if [[ "${has_failure}" -ne 0 ]]; then
    printf 'Read-only source verification failed.\n'
    exit 1
fi

printf 'All read-only source fingerprints match the approved baseline.\n'
