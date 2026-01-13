#!/usr/bin/env bash
set -euo pipefail
LC_ALL=C

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
GUARDRAIL_DIR="$REPO_ROOT/docs/SaaS/guardrails"

if [[ ! -d "$GUARDRAIL_DIR" ]]; then
  echo "Guardrail allowlist directory missing: $GUARDRAIL_DIR" >&2
  exit 1
fi

cd "$REPO_ROOT"

failures=0

normalize_lines() {
  local input="$1"
  local -n output="$2"
  output=()
  if [[ -z "$input" ]]; then
    return
  fi

  while IFS= read -r line; do
    line="${line//$'\r'/}"
    [[ -z "${line// }" ]] && continue
    output+=("$line")
  done < <(printf '%s\n' "$input" | sort -u)
}

compare_with_allowlist() {
  local key="$1"
  local description="$2"
  local actual_content="$3"
  local allowlist="$GUARDRAIL_DIR/$key.allowlist"
  local -a actual_lines
  local -a expected_lines

  normalize_lines "$actual_content" actual_lines

  if [[ -f "$allowlist" ]]; then
    mapfile -t expected_lines < <(grep -v -e '^[[:space:]]*$' -e '^[[:space:]]*#' "$allowlist")
    normalize_lines "$(printf '%s\n' "${expected_lines[@]}")" expected_lines
  else
    echo "ERROR: Guardrail allowlist missing or unreadable: $allowlist"
    failures=$((failures + 1))
    return
  fi

  local unexpected
  unexpected=$(comm -23 \
    <(printf '%s\n' "${actual_lines[@]}") \
    <(printf '%s\n' "${expected_lines[@]}"))

  local missing
  missing=$(comm -13 \
    <(printf '%s\n' "${actual_lines[@]}") \
    <(printf '%s\n' "${expected_lines[@]}"))

  if [[ -n "$unexpected" ]]; then
    echo "ERROR: Guardrail '$description' detected unexpected matches:"
    echo "$unexpected"
    failures=$((failures + 1))
  fi

  if [[ -n "$missing" ]]; then
    echo "NOTE: Guardrail '$description' allowlist contains stale entries:"
    echo "$missing"
    echo "Update $allowlist if these lines were intentionally removed."
  fi

  if [[ -z "$unexpected" ]]; then
    echo "Guardrail '$description' passed (${#actual_lines[@]} match(es))."
  else
    echo
  fi
}

run_dom_guard() {
  local matches
  matches=$(rg -n --path-separator / --no-heading -- 'innerHTML|outerHTML|insertAdjacentHTML|\.html\(' app/Views || true)
  compare_with_allowlist "dom-sinks" "DOM sinks in app/Views" "$matches"
}

run_group_guard() {
  local matches
  matches=$(
    {
      rg -F -n --path-separator / --no-heading --glob '!app/Helpers/**' -- 'in_group(' app || true
      rg -F -n --path-separator / --no-heading --glob '!app/Helpers/**' -- 'is_admin(' app || true
    } | sort -u
  )
  compare_with_allowlist "group-auth" "Group-based auth helpers" "$matches"
}

run_raw_queries_guard() {
  local matches
  matches=$(
    {
      rg -F -n --path-separator / --no-heading -- '->table(' app/Controllers || true
      rg -F -n --path-separator / --no-heading -- 'db->query' app/Controllers || true
    } | sort -u
  )
  compare_with_allowlist "raw-tenant-queries" "Raw tenant queries in controllers" "$matches"
}

run_dom_guard
run_group_guard
run_raw_queries_guard

if [[ $failures -ne 0 ]]; then
  echo "Guardrail validation failed ($failures unexpected match(es)). Fix the above issues."
  exit 1
fi

echo "SaaS guardrail validation succeeded."
