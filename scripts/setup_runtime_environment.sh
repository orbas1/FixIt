#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

APT_HAS_UPDATED=0
PHP_BIN=""
SEED_MARKER="$PROJECT_ROOT/storage/app/runtime/.db_seeded"

log() {
  echo "[setup] $*"
}

abort() {
  log "Error: $*"
  exit 1
}

command_exists() {
  command -v "$1" >/dev/null 2>&1
}

require_apt() {
  if ! command_exists apt-get; then
    abort "apt-get is required to install missing packages."
  fi
}

apt_install_packages() {
  local packages=()
  local pkg
  for pkg in "$@"; do
    if dpkg -s "$pkg" >/dev/null 2>&1; then
      continue
    fi
    packages+=("$pkg")
  done

  if ((${#packages[@]} == 0)); then
    return
  fi

  require_apt

  if [[ $APT_HAS_UPDATED -eq 0 ]]; then
    log "Updating apt package index..."
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y
    APT_HAS_UPDATED=1
  fi

  log "Installing packages: ${packages[*]}"
  export DEBIAN_FRONTEND=noninteractive
  apt-get install -y "${packages[@]}"
  hash -r
}

prepare_env_file() {
  if [[ ! -f .env ]]; then
    if [[ -f .env.example ]]; then
      log "Creating .env from .env.example"
      cp .env.example .env
    else
      log "No .env or .env.example found; creating default environment file."
      cat <<'ENV' > .env
APP_NAME=FixIt
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fixit
DB_USERNAME=fixit
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
ENV
    fi
  fi
}

load_env() {
  if [[ -f .env ]]; then
    # shellcheck disable=SC1091
    set -o allexport
    source .env
    set +o allexport
  fi
}

wait_for_mariadb() {
  local mysql_admin_cmd=("$1" "${@:2}")
  local retries=30
  log "Waiting for MariaDB to become ready..."
  until "${mysql_admin_cmd[@]}" ping --silent >/dev/null 2>&1; do
    ((retries--)) || abort "MariaDB did not start in time."
    sleep 1
  done
}

ensure_mariadb() {
  log "Ensuring MariaDB server is installed..."
  apt_install_packages mariadb-server mariadb-client

  local service_started=0
  if command_exists service; then
    if service mariadb status >/dev/null 2>&1; then
      service_started=1
    else
      if service mariadb start >/dev/null 2>&1 || service mysql start >/dev/null 2>&1; then
        service_started=1
      fi
    fi
  fi

  if [[ $service_started -eq 0 ]] && command_exists mysqld; then
    log "Starting MariaDB directly via mysqld --skip-networking."
    mysqld --skip-networking >/tmp/mysqld.log 2>&1 &
    disown || true
  fi

  local mysql_cli
  if command_exists mariadb; then
    mysql_cli=mariadb
  elif command_exists mysql; then
    mysql_cli=mysql
  else
    abort "Neither mariadb nor mysql client is available."
  fi

  local root_user=${DB_ROOT_USERNAME:-${MYSQL_ROOT_USERNAME:-root}}
  local root_password=${DB_ROOT_PASSWORD:-${MYSQL_ROOT_PASSWORD:-${MARIADB_ROOT_PASSWORD:-}}}

  local mysqladmin_args=(mysqladmin --protocol=socket --user="$root_user")
  if [[ -n "$root_password" ]]; then
    mysqladmin_args+=(--password="$root_password")
  fi
  wait_for_mariadb "${mysqladmin_args[@]}"

  local mysql_args=("$mysql_cli" --protocol=socket --user="$root_user")
  if [[ -n "$root_password" ]]; then
    mysql_args+=(--password="$root_password")
  fi

  local db_name=${DB_DATABASE:-fixit}
  local db_user=${DB_USERNAME:-fixit}
  local db_password=${DB_PASSWORD:-secret}
  local db_host=${DB_HOST:-127.0.0.1}

  local db_name_escaped=${db_name//\`/\`\`}
  local db_user_escaped=${db_user//\'/\'\'}
  local db_password_escaped=${db_password//\'/\'\'}

  local grant_hosts=()
  case "$db_host" in
    localhost)
      grant_hosts+=("localhost")
      ;;
    127.0.0.1)
      grant_hosts+=("127.0.0.1" "localhost")
      ;;
    ::1)
      grant_hosts+=("::1" "localhost")
      ;;
    *)
      grant_hosts+=("$db_host")
      if [[ "$db_host" != "%" ]]; then
        grant_hosts+=("%")
      fi
      ;;
  esac

  log "Configuring database '$db_name' and user '$db_user'."

  local sql="CREATE DATABASE IF NOT EXISTS \`$db_name_escaped\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n"
  local host
  local escaped_host
  for host in "${grant_hosts[@]}"; do
    escaped_host=${host//\'/\'\'}
    sql+="CREATE USER IF NOT EXISTS '$db_user_escaped'@'${escaped_host}' IDENTIFIED BY '$db_password_escaped';\n"
    sql+="ALTER USER '$db_user_escaped'@'${escaped_host}' IDENTIFIED BY '$db_password_escaped';\n"
    sql+="GRANT ALL PRIVILEGES ON \`$db_name_escaped\`.* TO '$db_user_escaped'@'${escaped_host}';\n"
  done
  sql+="FLUSH PRIVILEGES;"

  printf '%b' "$sql" | "${mysql_args[@]}"
}

database_has_rows() {
  local table=$1
  local db_name=${DB_DATABASE:-fixit}
  local db_user=${DB_USERNAME:-fixit}
  local db_password=${DB_PASSWORD:-secret}
  local db_host=${DB_HOST:-127.0.0.1}

  local cli
  if command_exists mariadb; then
    cli=mariadb
  elif command_exists mysql; then
    cli=mysql
  else
    return 1
  fi

  local -a cmd=("$cli" --batch --skip-column-names -u"$db_user" -D"$db_name" -e "SELECT 1 FROM \`$table\` LIMIT 1;")

  if [[ -n "$db_host" ]]; then
    cmd+=(--host="$db_host")
  fi

  if [[ "$db_host" == "127.0.0.1" || "$db_host" == "::1" || "$db_host" == "localhost" ]]; then
    cmd+=(--protocol=TCP)
  fi

  local mysql_pwd_backup=${MYSQL_PWD:-}
  if [[ -n "$db_password" && "$db_password" != "null" ]]; then
    export MYSQL_PWD="$db_password"
  else
    unset MYSQL_PWD
  fi

  local result
  result=$("${cmd[@]}" 2>/dev/null | head -n1)
  local status=$?

  if [[ -n "$mysql_pwd_backup" ]]; then
    export MYSQL_PWD="$mysql_pwd_backup"
  else
    unset MYSQL_PWD
  fi

  if [[ $status -ne 0 ]]; then
    return $status
  fi

  [[ -n "$result" ]]
}

ensure_php_runtime() {
  local php_packages=(
    php-cli
    php-common
    php-mysql
    php-mbstring
    php-xml
    php-curl
    php-zip
    php-bcmath
    php-gd
    php-intl
  )

  if command_exists php; then
    log "Detected existing PHP runtime; ensuring required packages are present."
  else
    log "PHP runtime not found; installing required PHP packages."
  fi

  apt_install_packages "${php_packages[@]}"

  local candidates=()
  [[ -n "${PHP_BINARY:-}" ]] && candidates+=("${PHP_BINARY}")
  [[ -n "${PHP_BIN_OVERRIDE:-}" ]] && candidates+=("${PHP_BIN_OVERRIDE}")
  [[ -n "${PHP_BIN}" ]] && candidates+=("${PHP_BIN}")
  candidates+=("/usr/bin/php" "php")

  local candidate resolved
  local fallback=""
  PHP_BIN=""
  for candidate in "${candidates[@]}"; do
    [[ -z "$candidate" ]] && continue
    if [[ -x "$candidate" ]]; then
      resolved="$candidate"
    elif command_exists "$candidate"; then
      resolved="$(command -v "$candidate")"
    else
      continue
    fi

    if "$resolved" -r "exit(extension_loaded('sodium') ? 0 : 1);" 2>/dev/null; then
      PHP_BIN="$resolved"
      break
    fi

    if [[ -z "$fallback" ]]; then
      fallback="$resolved"
    fi
  done

  if [[ -z "$PHP_BIN" && -n "$fallback" ]]; then
    PHP_BIN="$fallback"
  fi

  if [[ -z "$PHP_BIN" ]]; then
    abort "Unable to locate a PHP CLI binary."
  fi

  if ! "$PHP_BIN" -r "exit(extension_loaded('sodium') ? 0 : 1);" 2>/dev/null; then
    log "PHP sodium extension missing on $PHP_BIN; attempting to install php-sodium."
    apt_install_packages php-sodium libsodium23 || true
    if ! "$PHP_BIN" -r "exit(extension_loaded('sodium') ? 0 : 1);" 2>/dev/null; then
      log "Warning: PHP sodium extension is still unavailable on $PHP_BIN; continue after installing it manually if required."
    fi
  fi

  local php_dir
  php_dir="$(dirname "$PHP_BIN")"
  case ":$PATH:" in
    *:"$php_dir":*) ;;
    *) export PATH="$php_dir:$PATH" ;;
  esac

  log "Using PHP binary: $($PHP_BIN -v | head -n1)"
}

ensure_composer() {
  if command_exists composer; then
    log "Detected Composer: $(composer --version)"
  else
    log "Composer not found; installing..."
    apt_install_packages composer
  fi

  export COMPOSER_ALLOW_SUPERUSER=1
}

install_php_dependencies() {
  ensure_php_runtime
  ensure_composer

  log "Installing PHP dependencies via Composer..."
  local composer_bin
  composer_bin="$(command -v composer)"
  if [[ -z "$composer_bin" ]]; then
    abort "Composer executable not found after installation."
  fi
  "$PHP_BIN" "$composer_bin" install --prefer-dist --no-interaction --no-progress --ansi

  if ! grep -q '^APP_KEY=' .env || grep -q '^APP_KEY=$' .env; then
    log "Generating application key..."
    "$PHP_BIN" artisan key:generate --force
  fi
}

install_node_dependencies() {
  if ! command_exists npm; then
    log "npm not found; skipping Node dependency installation."
    return
  fi

  log "Installing Node dependencies via npm..."
  if [[ -f package-lock.json ]]; then
    npm ci
  else
    npm install
  fi
}

run_database_migrations() {
  if [[ ! -f artisan ]]; then
    log "artisan not found; skipping migrations."
    return
  fi

  if [[ -z "$PHP_BIN" ]]; then
    ensure_php_runtime
  fi

  log "Running database migrations..."
  "$PHP_BIN" artisan migrate --force

  if "$PHP_BIN" artisan list --no-ansi | grep -q '^  db:seed'; then
    local force_seed=${FORCE_DB_SEED:-0}
    local should_seed=1
    if [[ "$force_seed" != "1" ]]; then
      if [[ -f "$SEED_MARKER" ]]; then
        log "Seed marker found at $SEED_MARKER; skipping database seeders. Set FORCE_DB_SEED=1 to override."
        should_seed=0
      elif database_has_rows roles; then
        log "Detected existing role records; assuming database already seeded. Skipping db:seed."
        mkdir -p "$(dirname "$SEED_MARKER")"
        touch "$SEED_MARKER"
        should_seed=0
      fi
    fi

    if [[ $should_seed -eq 1 ]]; then
      log "Running database seeders..."
      "$PHP_BIN" artisan db:seed --force
      mkdir -p "$(dirname "$SEED_MARKER")"
      touch "$SEED_MARKER"
    fi
  fi
}

create_storage_links() {
  if [[ ! -f artisan ]]; then
    return
  fi

  log "Linking storage directory..."
  if [[ -z "$PHP_BIN" ]]; then
    ensure_php_runtime
  fi
  "$PHP_BIN" artisan storage:link || true
}

main() {
  prepare_env_file
  load_env
  ensure_mariadb
  install_php_dependencies
  install_node_dependencies
  create_storage_links
  run_database_migrations
  log "Environment setup complete."
}

main "$@"
