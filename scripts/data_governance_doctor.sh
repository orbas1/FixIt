#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")"/.. && pwd)"
cd "$ROOT_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "php is required to run data_governance_doctor.sh" >&2
  exit 1
fi

SQLITE_DB="$ROOT_DIR/storage/app/data_governance_doctor.sqlite"
export DB_CONNECTION=sqlite
export DB_DATABASE="$SQLITE_DB"

mkdir -p "$(dirname "$SQLITE_DB")"
touch "$SQLITE_DB"

php artisan migrate --force >/dev/null 2>&1
php artisan governance:data-doctor --format=json "$@" | python3 scripts/internal_data_governance_doctor.py
