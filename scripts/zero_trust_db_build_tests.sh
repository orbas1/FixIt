#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")"/.. && pwd)"
cd "$ROOT_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "php is required to run zero_trust_db_build_tests.sh" >&2
  exit 1
fi

php artisan test --testsuite=Feature --filter=ZeroTrustEvaluatorTest "$@"
