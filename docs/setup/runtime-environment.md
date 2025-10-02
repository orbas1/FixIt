# Runtime Environment Setup

This document explains how to provision a local runtime for the FixIt Laravel application in environments such as the CodeAssist container. The `scripts/setup_runtime_environment.sh` helper installs and configures MariaDB, prepares the `.env` file, installs dependencies, and runs database migrations/seeders.

## Prerequisites

* Debian/Ubuntu based host with `sudo` or root access (the helper currently uses `apt-get` to install missing packages)
* PHP 8.2+, Composer, and Node.js 18+/npm (Node is optional for API-only work)
  *The setup script will install PHP CLI + common extensions and Composer when missing on Debian/Ubuntu hosts.*
* Internet access to download apt packages and Composer dependencies

## Usage

```bash
./scripts/setup_runtime_environment.sh
```

The script performs the following steps:

1. **Environment file** – Creates `.env` from `.env.example` (or generates a default) if none exists.
2. **Database** – Installs MariaDB server/client via `apt`, starts the service, and provisions the `fixit` database with a local user/password (`fixit` / `secret`). Values honour existing `DB_*` variables defined in `.env`.
3. **PHP runtime & dependencies** – Installs PHP CLI/common extensions and Composer when required, runs `composer install`, and generates the Laravel application key when missing.
4. **Node dependencies** – Runs `npm ci` (or `npm install` when the lock file is absent) when npm is available.
5. **Storage link** – Executes `php artisan storage:link`.
6. **Migrations & seeders** – Executes `php artisan migrate --force` and (on the first run) `php artisan db:seed --force`. Subsequent runs skip seeding if `storage/app/runtime/.db_seeded` exists; remove the marker or set `FORCE_DB_SEED=1` to reseed.

> **Note:** The helper prefers the system PHP binary (e.g. `/usr/bin/php`) to ensure the bundled sodium extension is available even when tools like `mise` provide an alternative PHP on `$PATH`.

## Customisation

Override database credentials by editing `.env` before running the script or by exporting environment variables, for example:

```bash
export DB_DATABASE=fixit_dev
export DB_USERNAME=devuser
export DB_PASSWORD=devpass
./scripts/setup_runtime_environment.sh
```

## Troubleshooting

* **MariaDB fails to start** – Check `/var/log/mysql/error.log`. The script waits up to 30 seconds for the socket to become available.
* **Composer memory issues** – Set `COMPOSER_MEMORY_LIMIT=-1` before running the script.
* **npm missing** – Install Node.js (e.g. via `nvm` or apt) or skip front-end builds. The script logs and continues when npm is unavailable.

## Next steps

After the script completes you can boot the Laravel development server:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Then access the application at `http://localhost:8000` (or the forwarded port in your environment).
