#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPORT_PATH="$PROJECT_ROOT/storage/logs/env-doctor-report.json"
MODE="local"
declare -a CHECK_TARGETS=()

for arg in "$@"; do
  case "$arg" in
    --mode=ci)
      MODE="ci"
      ;;
    --check=*)
      CHECK_TARGETS+=("${arg#*=}")
      ;;
    *)
      echo "[env-doctor] Unknown argument: $arg" >&2
      exit 64
      ;;
  esac
done

mkdir -p "$(dirname "$REPORT_PATH")"

declare -a REQUIRED_KEYS=(
  "APP_KEY"
  "DB_PASSWORD"
  "PUSHER_APP_KEY"
  "PUSHER_APP_SECRET"
  "AWS_ACCESS_KEY_ID"
  "AWS_SECRET_ACCESS_KEY"
)

declare -a PROHIBITED_VALUES=(
  "changeme"
  "change_me"
  "secret"
  "password"
  "your apiurl"
  "your paymenturl"
  "your providerappurl"
)

issues=()
has_error=0

json_escape() {
  python - <<'PY' "$1"
import json, sys
print(json.dumps(sys.argv[1]))
PY
}

add_issue() {
  local severity="$1"
  local scope="$2"
  local message="$3"
  local escaped_scope
  local escaped_message
  escaped_scope=$(json_escape "$scope")
  escaped_message=$(json_escape "$message")
  issues+=("{\"severity\":\"$severity\",\"scope\":$escaped_scope,\"message\":$escaped_message}")
  if [[ "$severity" == "error" ]]; then
    has_error=1
  fi
}

read_env_value() {
  local file="$1"
  local key="$2"
  local line
  line=$(grep -E "^[[:space:]]*${key}=" "$file" | tail -n1 || true)
  if [[ -z "$line" ]]; then
    return 1
  fi
  line=${line#*=}
  line="${line//$'\r'/}"
  line="${line//$'\n'/}"
  line=$(printf '%s' "$line" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')
  if [[ ${#line} -ge 2 ]]; then
    local first_char="${line:0:1}"
    local last_char="${line: -1}"
    if [[ "$first_char" == '"' && "$last_char" == '"' ]]; then
      line="${line:1:${#line}-2}"
    elif [[ "$first_char" == "'" && "$last_char" == "'" ]]; then
      line="${line:1:${#line}-2}"
    fi
  fi
  echo "$line"
  return 0
}

value_is_prohibited() {
  local value="${1,,}"
  for candidate in "${PROHIBITED_VALUES[@]}"; do
    if [[ "$value" == "$candidate" ]]; then
      return 0
    fi
  done
  return 1
}

check_env_file() {
  local file="$1"
  local scope_label="$2"
  local required_strict="$3"

  if [[ ! -f "$file" ]]; then
    add_issue "warning" "$scope_label" "Environment file $file not found."
    return
  fi

  for key in "${REQUIRED_KEYS[@]}"; do
    local value
    if ! value=$(read_env_value "$file" "$key"); then
      add_issue "error" "$scope_label" "Missing required key $key in $file."
      continue
    fi
    if [[ -z "$value" ]]; then
      if [[ "$required_strict" == "strict" ]]; then
        add_issue "error" "$scope_label" "Key $key in $file is empty."
      else
        add_issue "warning" "$scope_label" "Key $key in $file is empty (expected for templates)."
      fi
      continue
    fi
    if value_is_prohibited "$value"; then
      local severity="warning"
      if [[ "$required_strict" == "strict" ]]; then
        severity="error"
      fi
      add_issue "$severity" "$scope_label" "Key $key in $file uses prohibited placeholder value '$value'."
    fi
  done
}

check_flutter_environment() {
  local flutter_env="$PROJECT_ROOT/apps/provider/lib/services/environment.dart"
  if [[ ! -f "$flutter_env" ]]; then
    add_issue "error" "flutter" "Missing Flutter environment configuration at $flutter_env."
    return
  fi
  if grep -q "Your ApiUrl" "$flutter_env" || grep -q "Your paymentUrl" "$flutter_env"; then
    add_issue "error" "flutter" "Flutter environment.dart still references placeholder URLs."
  fi
}

check_checklist_json() {
  local checklist="$PROJECT_ROOT/apps/provider/assets/config/environment_checklist.json"
  if [[ ! -f "$checklist" ]]; then
    add_issue "error" "checklist" "Environment checklist asset not found at $checklist."
    return
  fi

  local output
  local status=0
  if ! output=$(python "$PROJECT_ROOT/scripts/internal_env_doctor.py" "$checklist" "$MODE" "${CHECK_TARGETS[@]:-}" 2>&1); then
    status=$?
  fi
  if [[ -n "$output" ]]; then
    while IFS= read -r line; do
      [[ -z "$line" ]] && continue
      local severity="${line%%::*}"
      local rest="${line#*::}"
      local scope="${rest%%::*}"
      local message="${rest#*::}"
      severity=${severity,,}
      add_issue "$severity" "$scope" "$message"
    done <<< "$output"
  fi
  if [[ $status -ne 0 ]]; then
    has_error=1
  fi
}

check_env_file "$PROJECT_ROOT/.env" "laravel:.env" "strict"
check_env_file "$PROJECT_ROOT/.env.example" "laravel:.env.example" "template"
check_flutter_environment
check_checklist_json

status_label="pass"
if [[ $has_error -ne 0 ]]; then
  status_label="fail"
fi

timestamp=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
{
  printf '{\n'
  printf '  "checked_at": %s,\n' "$(json_escape "$timestamp")"
  printf '  "mode": %s,\n' "$(json_escape "$MODE")"
  printf '  "status": %s,\n' "$(json_escape "$status_label")"
  printf '  "issues": [\n'
  if ((${#issues[@]} > 0)); then
    for i in "${!issues[@]}"; do
      printf '    %s' "${issues[$i]}"
      if (( i + 1 < ${#issues[@]} )); then
        printf ','
      fi
      printf '\n'
    done
  fi
  printf '  ]\n'
  printf '}\n'
} > "$REPORT_PATH"

if [[ $status_label == "fail" ]]; then
  echo "[env-doctor] FAILED — see $REPORT_PATH for details." >&2
  exit 1
fi

echo "[env-doctor] PASS — report written to $REPORT_PATH"
