#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPORT_PATH="$PROJECT_ROOT/storage/logs/architecture-governance-report.json"
ASSET_PATH="$PROJECT_ROOT/apps/provider/assets/config/architecture_governance_checklist.json"
APPENDIX_PATH="$PROJECT_ROOT/docs/appendices/appendix-d-architecture-governance.md"
MODE="local"

for arg in "$@"; do
  case "$arg" in
    --mode=ci)
      MODE="ci"
      ;;
    --asset=*)
      ASSET_PATH="${arg#*=}"
      ;;
    --appendix=*)
      APPENDIX_PATH="${arg#*=}"
      ;;
    *)
      echo "[architecture-doctor] Unknown argument: $arg" >&2
      exit 64
      ;;
  esac
done

mkdir -p "$(dirname "$REPORT_PATH")"

PYTHON_SCRIPT="$PROJECT_ROOT/scripts/internal_architecture_governance_doctor.py"

if [[ ! -f "$PYTHON_SCRIPT" ]]; then
  echo "[architecture-doctor] Internal validator missing at $PYTHON_SCRIPT" >&2
  exit 1
fi

set +e
json_output=$("$PYTHON_SCRIPT" --asset "$ASSET_PATH" --appendix "$APPENDIX_PATH")
exit_code=$?
set -e

if [[ -z "$json_output" ]]; then
  echo "[architecture-doctor] Validator produced no output." >&2
  exit 1
fi

printf '%s\n' "$json_output" > "$REPORT_PATH"

ARCHI_JSON="$json_output" python - "$MODE" <<'PY'
import json
import os
import sys

payload = os.environ.get("ARCHI_JSON", "")
if not payload:
    raise SystemExit("[architecture-doctor] Empty JSON payload")

data = json.loads(payload)
mode = sys.argv[1]
print(f"[architecture-doctor] Mode: {mode}")
print(f"[architecture-doctor] Status: {data.get('status', 'unknown')}")
print(
    f"[architecture-doctor] Capabilities: {data.get('capability_count', 0)} | Dependency coverage: {data.get('dependency_coverage', 0):.2f}"
)
issues = data.get('issues', [])
if not isinstance(issues, list):
    issues = []
if issues:
    print("[architecture-doctor] Issues:")
    for issue in issues:
        severity = issue.get('severity', 'info')
        scope = issue.get('scope', 'general')
        message = issue.get('message', '')
        print(f"  - {severity.upper()} [{scope}] {message}")
else:
    print("[architecture-doctor] No issues detected.")
PY

if [[ $exit_code -ne 0 ]]; then
  exit $exit_code
fi

exit 0
