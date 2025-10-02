# FixIt

## Runtime Setup

To provision a fully working local environment inside this container, run:

```bash
./scripts/setup_runtime_environment.sh
```

The helper script installs MariaDB, prepares the Laravel `.env`, installs (or bootstraps) PHP/Composer/npm dependencies, runs migrations/seeders (idempotently), and links storage. See [docs/setup/runtime-environment.md](docs/setup/runtime-environment.md) for details and troubleshooting.

## Browser view previews

Laravel Dusk is installed and configured so you can exercise Blade views in a full browser and collect screenshots when styles break.

1. Copy the example environment if you don't already have one: `cp .env.dusk.local.example .env.dusk.local`.
2. List the views you want to inspect inside [`tests/Browser/views.php`](tests/Browser/views.php). Each entry can optionally include seeded data, a custom viewport, and a selector to wait for before the screenshot is captured.
3. Run the view-only suite: `php artisan dusk --group=view-previews`. Dusk boots a headless Chrome session, renders each view in isolation via the `/__dusk/view-preview` route, and stores screenshots under `tests/Browser/screenshots/`.
4. Inspect the generated screenshots or HTML source (`tests/Browser/source/`) to spot layout and CSS regressions without leaving the container.

The headless Chrome driver is bundled automatically; no extra services are required.
