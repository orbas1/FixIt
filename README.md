# FixIt Monorepo (from /opt/fixit)

Contains:
- **Laravel** web app (repo root)
- **Mobile apps** under `apps/`:
  - `apps/provider` (Flutter)
  - `apps/user` (Flutter)

## Setup
- Copy `.env.example` → `.env` and fill secrets locally (never commit secrets).
- Keep mobile Firebase files out of Git: `google-services.json`, `GoogleService-Info.plist`.
