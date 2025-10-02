#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CHECKLIST_PATH="$PROJECT_ROOT/apps/provider/assets/config/i18n_currency_checklist.json"
REPORT_PATH="$PROJECT_ROOT/storage/logs/i18n-currency-doctor.json"
PYTHON_BIN="python3"

for arg in "$@"; do
  case "$arg" in
    --checklist=*)
      CHECKLIST_PATH="${arg#*=}"
      ;;
    --python=*)
      PYTHON_BIN="${arg#*=}"
      ;;
    *)
      echo "[i18n-doctor] Unknown argument: $arg" >&2
      exit 64
      ;;
  esac
done

if [[ ! -f "$CHECKLIST_PATH" ]]; then
  echo "[i18n-doctor] Checklist not found: $CHECKLIST_PATH" >&2
  exit 1
fi

mkdir -p "$(dirname "$REPORT_PATH")"

if ! output="$($PYTHON_BIN "$PROJECT_ROOT/scripts/internal_i18n_currency_doctor.py" --checklist "$CHECKLIST_PATH" --json)"; then
  status=$?
  echo "$output" | tee "$REPORT_PATH" >&2
  echo "[i18n-doctor] FAILED" >&2
  exit "$status"
fi

echo "$output" | tee "$REPORT_PATH" >/dev/null

status_value="$($PYTHON_BIN - "$REPORT_PATH" <<'PY'
import json
import sys
path = sys.argv[1]
try:
    with open(path, encoding="utf-8") as handle:
        data = json.load(handle)
    print(data.get("status", "error"))
except Exception:
    print("error")
PY
)"

if [[ "$status_value" != "ok" ]]; then
  echo "[i18n-doctor] FAILED" >&2
  exit 1
fi

echo "[i18n-doctor] PASS — report written to $REPORT_PATH"
