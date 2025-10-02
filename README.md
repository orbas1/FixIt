# FixIt

## Runtime Setup

To provision a fully working local environment inside this container, run:

```bash
./scripts/setup_runtime_environment.sh
```

The helper script installs MariaDB, prepares the Laravel `.env`, installs (or bootstraps) PHP/Composer/npm dependencies, runs migrations/seeders (idempotently), and links storage. See [docs/setup/runtime-environment.md](docs/setup/runtime-environment.md) for details and troubleshooting.
