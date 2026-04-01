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
RG_PATH_SEPARATOR="/"
case "${OSTYPE:-}" in
  msys*|cygwin*|win32*)
    # Git Bash/MSYS rewrites `/` option values; `//` preserves a literal slash for rg.
    RG_PATH_SEPARATOR="//"
    ;;
esac

run_rg() {
  rg --path-separator "$RG_PATH_SEPARATOR" "$@"
}

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
  matches=$(run_rg -n --no-heading -- 'innerHTML|outerHTML|insertAdjacentHTML|\.html\(' app/Views || true)
  compare_with_allowlist "dom-sinks" "DOM sinks in app/Views" "$matches"
}

run_group_guard() {
  local matches
  matches=$(
    {
      run_rg -F -n --no-heading --glob '!app/Helpers/**' -- 'in_group(' app || true
      run_rg -F -n --no-heading --glob '!app/Helpers/**' -- 'is_admin(' app || true
    } | sort -u
  )
  compare_with_allowlist "group-auth" "Group-based auth helpers" "$matches"
}

run_raw_queries_guard() {
  local matches
  matches=$(
    {
      run_rg -F -n --no-heading -- '->table(' app/Controllers || true
      run_rg -F -n --no-heading -- 'db->query' app/Controllers || true
    } | sort -u
  )
  compare_with_allowlist "raw-tenant-queries" "Raw tenant queries in controllers" "$matches"
}

run_forbidden_font_guard() {
  local matches
  matches=$(
    run_rg -n --no-heading -i \
      --glob '!app/Views/contracts/print.php' \
      --glob '!app/Views/finance/invoice.php' \
      --glob '!app/Views/inventory/index.php' \
      --glob '!app/Views/inventory/low_stock.php' \
      --glob '!app/Views/inventory/usage_history.php' \
      --glob '!app/Views/inventory/usage_print.php' \
      --glob '!app/Views/layouts/print.php' \
      --glob '!app/Views/layouts/main_auth.php' \
      --glob '!app/Views/layouts/main_control_plane.php' \
      --glob '!app/Views/layouts/kiosk.php' \
      --glob '!app/Views/odontogram/export.php' \
      --glob '!app/Views/odontogram/pdf.php' \
      --glob '!app/Views/odontogram/print.php' \
      --glob '!app/Views/welcome_message.php' \
      --glob '!app/Views/errors/html/error_400.php' \
      --glob '!app/Views/errors/html/error_404.php' \
      --glob '!app/Views/finance/invoice.php' \
      '(Arial|Segoe UI|Tahoma|Verdana|Helvetica Neue|Helvetica|Times New Roman|Georgia)' app/Views || true
  )

  if [[ -n "$matches" ]]; then
    echo "ERROR: Forbidden UI font-family strings detected outside approved print/export surfaces:"
    echo "$matches"
    failures=$((failures + 1))
  else
    echo "Guardrail 'Forbidden UI fonts' passed."
  fi
}

run_dom_guard
run_group_guard
run_raw_queries_guard
run_forbidden_font_guard

if [[ $failures -ne 0 ]]; then
  echo "Guardrail validation failed ($failures unexpected match(es)). Fix the above issues."
  exit 1
fi

echo "SaaS guardrail validation succeeded."
