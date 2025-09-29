# Fixit + Taskup Full Upgrade Guide (Web + Mobile) — Developer Playbook for GPT‑Codex

**Scope:** End‑to‑end upgrade of **Fixit** (local services marketplace) and companion **Taskup** components across **Laravel (PHP 8.2+) web app + Admin**, and **Flutter (Dart 3) iOS/Android user & provider Mobile apps**. Deliver: Airtasker‑style Live Feed, global tax & zones, unified search, escrow + disputes, storefront/eCommerce for tools, affiliates, ads, security, and full UI/UX overhaul. Includes database migrations, APIs, background jobs, analytics, observability, and CI/CD.

> **Stack Assumptions**
>
> * Web: Laravel 10/11 (PHP 8.2+), MySQL 8.x, Redis (cache/queue), Horizon, Scout + Meilisearch/Elasticsearch, Sanctum/JWT, Pusher/Socket.IO, S3‑compatible storage, Maps (Google/OSM), Geospatial (MySQL SRID 4326 + H3).
> * Mobile: Flutter 3.x, Riverpod/Bloc, Dio/Retrofit, Firebase (FCM), Sentry, in‑app purchases (optional later), Google/Apple Pay via Stripe.
> * DevOps: Nginx/Apache, Node 20 for asset build (Vite), Docker for local, GitHub Actions CI/CD, Fastly/Cloudflare CDN, Imgproxy/Thumbor.



# Fixit + Taskup Upgrade — **Sections 0 & 1 (Group 1/?)**

> This document replaces the canvas and focuses **only** on the sections you provided, in deep implementation detail. Subsequent groups of 5 can be appended later.

---

# 0) High‑Level Program Plan (Expanded for Execution)

This section converts the phases into **actionable workstreams**, acceptance criteria, owners, and hard deliverables that GPT‑Codex or a dev squad can execute without ambiguity.

## Phase 1 — Foundation & Hardening (Week 1)

**Objective:** Establish safe, observable, reproducible environments and a stable developer experience.

### 0.1 Environments & Secrets

* **Tasks**

  * Create `.env.example` and `.env.production` templates for Web (Laravel) and Mobile CI (Fastlane).
  * Move secrets to **environment variables**; integrate with parameter store (e.g., AWS SSM or Doppler). Keys: `STRIPE_*`, `ESCROW_*`, `GOOGLE_MAPS_KEY`, `PUSHER_*`, `MEILISEARCH_*`, `SENTRY_*`, `SHIP_*`.
  * Provide key rotation playbook; secrets never committed.
* **Deliverables**

  * `docs/devops/secrets.md` (rotation, ownership, retrieval), `.env.*` templates, CI masked secrets.
* **Acceptance**: Secrets redacted from repo; CI redacts logs; local build works using `.env.local`.

### 0.2 Logging, Metrics, Tracing

* **Stack**: Laravel Telescope (dev), Monolog → JSON → Loki/ELK; Sentry for errors; OpenTelemetry exporter optional.
* **Deliverables**: `config/logging.php` JSON channel; `SENTRY_DSN` in env; HTTP access logs streamed; correlation ID middleware.
* **Acceptance**: 100% API endpoints include `X-Request-ID`; error triage in Sentry/SLA 24h.

### 0.3 Single Sign‑On (optional) + Auth Hardening

* **Tasks**: Sanctum/JWT finalized, MFA (TOTP), device session table, passwordless magic links, OAuth (Google/Apple) on mobile.
* **Acceptance**: Sign‑in flows tested (password, magic link, Google/Apple), MFA recovery codes downloadable, device revoke works.

### 0.4 Rate Limiting & Abuse Controls

* **Tasks**: Laravel `ThrottleRequests` policies per route group; IP/device fingerprinting; bot challenge for sensitive posts (jobs/bids/chat attachments).
* **Acceptance**: Load test demonstrates graceful 429s with retry headers; no unbounded endpoints.

### 0.5 Queues & Background Jobs

* **Stack**: Redis + Horizon; queues: `default`, `media`, `search`, `payments`, `disputes`, `mail`, `ads`.
* **Acceptance**: Horizon dashboard healthy; retry/backoff policies defined; failed jobs DLQ retained 30 days.

### 0.6 Test Harness

* **Stack**: PHPUnit + Pest; Cypress for web E2E; Flutter integration tests. Factories/seeders for jobs, bids, stores, products, affiliates.
* **Acceptance**: CI green on unit + API + minimal E2E; seed script generates 100 demo items per domain.

---

## Phase 2 — Core Data Model & Migrations (Week 1–2)

**Objective:** Introduce all new domain tables with referential integrity, spatial indexes, and seeders.

* **Deliverables**: All migrations, models, policies, factories; ERD; reindex script for search; backfill jobs (geo + H3) for legacy rows.
* **Acceptance**: `php artisan migrate:fresh --seed` boots a fully navigable demo.

---

## Phase 3 — API Contracts & Services (Week 2–4)

**Objective:** Implement REST + WebSocket with consistent pagination and filtering.

* **Deliverables**: Versioned routes `/api/v1`, OpenAPI spec, Socket channels (`feed`, `job.{id}.*`).
* **Acceptance**: Postman collection passes; WebSocket broadcasts on create/bid/comment.

---

## Phase 4 — Web UI/UX Overhaul (Week 3–6)

**Objective:** Landing‑before‑login, unified search grid, modern dashboards, admin redesign.

* **Deliverables**: Tailwind tokens, component library (Cards, Grids), accessibility pass (WCAG 2.2 AA).
* **Acceptance**: LCP < 2.5s; keyboard nav across critical flows.

---

## Phase 5 — Mobile Apps Parity (Week 4–7)

**Objective:** Feature parity for feed/search/jobs/bids/escrow/disputes/store/affiliates.

* **Deliverables**: Flutter modules, Retrofit clients, Riverpod states, push notifications.
* **Acceptance**: 12 TestFlight/Play testers complete the smoke script without blockers.

---

## Phase 6 — Performance & Security (Week 5–7)

**Objective:** Caching, indexes, bundle splitting, CSP/WAF, SAST/DAST.

* **Deliverables**: Measured p95 latency < 400ms API; CSP headers; OWASP ZAP baseline report.
* **Acceptance**: No high‑severity vulns; core pages pass Core Web Vitals.

---

## Phase 7 — UAT & Launch (Week 7–8)

**Objective:** Test suites, seeders, migration plan/rollback, runbooks.

* **Deliverables**: `RUNBOOK.md`, `ROLLBACK.md`, migration order, smoke test checklist.
* **Acceptance**: Dry‑run deployment + rollback on staging; go/no‑go checklist signed.

**Module Gate**: ✅ Unit > ✅ Integration > ✅ E2E > ✅ Security > ✅ Perf > ✅ Accessibility > ✅ i18n.

---

# 1) Data Model (Migrations Cheatsheet — Production‑Ready)

General rules for all migrations:

* **Collation**: `utf8mb4_unicode_ci`; **Engine**: InnoDB; **IDs**: bigIncrements internally, UUIDs for public references via `ulid()` or `uuid()` with index.
* **Timestamps**: `created_at`, `updated_at`; **Soft deletes** where reversibility is desirable.
* **FKs**: `onDelete('cascade')` where child has no meaning without parent; otherwise `restrict`.
* **Indexes**: composite for frequent filters; cover geo with spatial + H3.

## 1.1 Live Feed & Marketplace

**Purpose:** Power an Airtasker‑style real‑time feed: jobs with photos, geo, bids, Q&A, and activity events.

### Tables & Fields

* **jobs** (extend existing)

  * Add: `feed_visibility ENUM('public','private') DEFAULT 'public'`
  * Add: `geo_point POINT SRID 4326 NULL` + SPATIAL INDEX `jobs_geo_idx`.
  * Add: `h3_index BIGINT UNSIGNED NULL` (resolution 8–9) + INDEX `jobs_h3_idx`.
  * Add: `attachments JSON NULL`, `status ENUM('draft','open','assigned','in_progress','completed','cancelled') DEFAULT 'open'`, `budget_min DECIMAL(10,2) NULL`, `budget_max DECIMAL(10,2) NULL`, `currency CHAR(3) DEFAULT 'USD'`.

* **job_items** (optional breakdown)

  * `id`, `job_id` FK → jobs, `title VARCHAR(200)`, `qty DECIMAL(10,3)`, `unit_price DECIMAL(10,2)`.

* **bids**

  * `id`, `job_id` FK, `provider_id` FK → users/providers, `amount DECIMAL(10,2)`, `message TEXT`, `attachments JSON`, `status ENUM('pending','accepted','declined','withdrawn') DEFAULT 'pending'`, `expires_at DATETIME NULL`.
  * Indexes: `(job_id,status)`, `(provider_id, status)`.

* **job_questions** (Q&A + threads)

  * `id`, `job_id` FK, `user_id` FK, `body TEXT`, `parent_id` self‑FK NULL, `is_answer BOOLEAN DEFAULT 0`.

* **job_events** (activity → feed)

  * `id`, `job_id` FK, `actor_id` FK, `type ENUM('created','bid_placed','bid_withdrawn','comment','question','answer','status_changed','assigned')`, `payload JSON`, `created_at`.
  * Indexes: `(job_id, created_at)`, `(type, created_at)`.

* **media** (polymorphic uploads)

  * `id`, `owner_type VARCHAR(80)`, `owner_id BIGINT`, `kind ENUM('image','doc','video','other')`, `path VARCHAR(512)`, `width INT NULL`, `height INT NULL`, `mime VARCHAR(120)`, `blurhash VARCHAR(120) NULL`.
  * Index: `(owner_type, owner_id)`.

### Implementation Notes

* **Geo/H3**: compute H3 index in service layer on create/update; backfill job.
* **Events**: every mutating action emits `job_events` and a WebSocket broadcast.
* **Attachments**: Upload to S3; scan via `file_scans` (see 1.9) before publish.

### Acceptance

* Creating a job triggers an event; feed query by distance/h3 returns expected set; bids/Q&A appear in real time; attachments render previews.

---

## 1.2 Escrow & Payments

**Purpose:** Hold buyer funds until service completion; support partial refunds and immutable ledger.

### Tables & Fields

* **escrows**

  * `id`, `job_id` FK, `payer_id` FK, `payee_id` FK, `currency CHAR(3)`, `amount DECIMAL(10,2)`, `fee_pct DECIMAL(5,2) DEFAULT 0`, `processor ENUM('stripe','escrow_com','manual')`, `processor_ref VARCHAR(120)`, `status ENUM('pending','funded','held','released','refunded','partially_refunded','failed') DEFAULT 'pending'`, `held_at`, `released_at`, `refunded_at`.
  * Indexes: `(job_id,status)`, `(payer_id,status)`, `(payee_id,status)`.

* **transactions**

  * `id`, `escrow_id` FK, `type ENUM('charge','transfer','refund','fee')`, `amount DECIMAL(10,2)`, `currency CHAR(3)`, `processor ENUM('stripe','escrow_com','manual')`, `processor_ref VARCHAR(120)`, `status ENUM('succeeded','pending','failed')`, `meta JSON`.

### Implementation Notes

* Use Stripe PaymentIntents with `transfer_group`. On release: create transfer to provider; affiliate fees booked as `fee` transactions.
* Enforce idempotency keys per operation.

### Acceptance

* Funding creates `escrows(funded)` and a `transactions(charge)`; releasing creates `transactions(transfer)`; partial refund math reconciles to zero drift.

---

## 1.3 Disputes (Multi‑Stage)

**Purpose:** Structured resolution workflow: negotiation → mediation → arbitration.

### Tables & Fields

* **disputes**

  * `id`, `job_id` FK, `opened_by_id` FK, `against_id` FK, `stage ENUM('negotiation','mediation','arbitration','closed') DEFAULT 'negotiation'`, `status ENUM('open','settled','refunded','escalated','rejected') DEFAULT 'open'`, `claim_amount DECIMAL(10,2)`, `resolution JSON NULL`, `deadline_at DATETIME`.

* **dispute_messages**

  * `id`, `dispute_id` FK, `author_id` FK, `body TEXT`, `attachments JSON`, `visibility ENUM('parties','admin_only') DEFAULT 'parties'`.

* **dispute_events**

  * `id`, `dispute_id` FK, `actor_id` FK, `from_stage`, `to_stage`, `status`, `note TEXT`, `created_at`.

### Implementation Notes

* Timers: cron checks deadlines, auto‑advance flows; notifications on stage change.
* Visibility: admins can post `admin_only` messages; parties see only permitted.

### Acceptance

* Open → message exchange → escalate → admin settlement → payout/refund updates escrow accordingly; audit trail complete.

---

## 1.4 Taxes & Zones

**Purpose:** Provider‑declared tax profiles + precise geo zones for jobs and eCommerce shipping.

### Tables & Fields

* **tax_profiles**

  * `id`, `owner_type ENUM('user','business')`, `owner_id`, `country CHAR(2)`, `region VARCHAR(80) NULL`, `locality VARCHAR(120) NULL`, `postcode VARCHAR(32) NULL`, `tax_id VARCHAR(64) NULL`, `scheme ENUM('flat','progressive','vat','gst','sales')`, `rate DECIMAL(5,2) NULL`, `nexus JSON NULL`, `effective_from DATE`, `effective_to DATE NULL`, `is_default BOOLEAN DEFAULT 0`.

* **zone_polygons**

  * `id`, `name`, `country CHAR(2)`, `level ENUM('country','region','city','custom')`, `srid INT DEFAULT 4326`, `polygon GEOMETRY NOT NULL SRID 4326`, `h3_res TINYINT DEFAULT 8`, `meta JSON`.
  * Indexes: SPATIAL INDEX on `polygon`.

* **geo_cache**

  * `id`, `method ENUM('forward','reverse')`, `query_hash CHAR(64)`, `input JSON`, `result JSON`, `expires_at DATETIME`.

### Implementation Notes

* Prefer **components geocoding** (country + postal_code). Cache responses; checksum inputs.
* Tax computation service reads `tax_profiles` (owner/provider) + `job.geo` or `order.shipping_address` to resolve jurisdiction and rate.

### Acceptance

* Providers can create multiple profiles; selecting a default applies to jobs/orders; invoices show tax breakdown lines.

---

## 1.5 Unified Search & Matching

**Purpose:** One search endpoint across services, providers, jobs, and store products; ranking by relevance + proximity.

### Tables & Fields

* **tags**: `id`, `slug`, `name`, `kind ENUM('skill','service','tool','category')` (unique `slug+kind`).
* **taggables**: `tag_id`, `taggable_type`, `taggable_id` (composite index).
* **search_snapshots**: `id`, `subject_type`, `subject_id`, `doc JSON`, `updated_at` (indexed); materializes render‑ready docs for Scout.
* **ratings_agg**: `user_id` unique, `avg_rating DECIMAL(3,2)`, `jobs_done INT`, `on_time_pct DECIMAL(5,2)`.

### Implementation Notes

* Indexer jobs write to Scout/Meilisearch; LFU cache for hot queries; query parser transforms single‑box input + filters to typed query.

### Acceptance

* Search returns mixed entity results under 300ms p95; filters/chips refine; ranking passes relevance tests.

---

## 1.6 Ads/Banners & Placements

**Purpose:** Configurable ad slots with targeting, frequency capping, and metrics.

### Tables & Fields

* **ad_campaigns**: `id`, `owner_id`, `name`, `budget DECIMAL(12,2)`, `cpc DECIMAL(8,4)`, `cpm DECIMAL(8,4)`, `status ENUM('draft','active','paused','ended')`, `targeting JSON`.
* **ad_creatives**: `id`, `campaign_id` FK, `type ENUM('banner','card','feed_promo')`, `asset`, `cta`, `landing_url`, `meta JSON`.
* **ad_impressions** & **ad_clicks**: append‑only with `user_id` nullable, `slot`, `creative_id`, `ts`.

### Implementation Notes

* Redis keys for frequency capping per user/slot/day; signed tracking pixel endpoints.

### Acceptance

* Ads render in slots with correct targeting; impressions/clicks reconcile; capping enforced.

---

## 1.7 Affiliates

**Purpose:** Track referrals, attribute conversions, and manage payouts.

### Tables & Fields

* **affiliates**: `id`, `user_id`, `code`, `status`, `tier`, `payout_method`, `rate_pct DECIMAL(5,2)`, `cookie_days INT`.
* **affiliate_links**: `id`, `affiliate_id` FK, `channel`, `slug`, `destination`, `utm JSON`.
* **affiliate_attributions**: `id`, `affiliate_id` FK, `user_id`, `first_touch_at`, `last_touch_at`, `source`.
* **affiliate_payouts**: `id`, `affiliate_id` FK, `period`, `amount DECIMAL(10,2)`, `currency`, `status`, `evidence JSON`.

### Acceptance

* Link → signup attribution recorded; payout ledger produced monthly; export CSV for finance.

---

## 1.8 Storefront: Provider Tools eCommerce

**Purpose:** Allow providers to sell tools/products with shipping, tax, and disputes when needed.

### Tables & Fields

* **stores**: `id`, `owner_id`, `slug`, `name`, `logo`, `about TEXT`, `policies JSON`.
* **products**: `id`, `store_id` FK, `sku`, `name`, `desc TEXT`, `price DECIMAL(10,2)`, `currency`, `stock INT`, `weight DECIMAL(10,3)`, `dimensions JSON`, `shipping_profile_id FK`, `media JSON`, `attributes JSON`, `status ENUM('draft','active','paused')`.
* **shipping_profiles**: `id`, `store_id` FK, `rules JSON`, `carrier`, `flat_rate DECIMAL(10,2) NULL`, `free_over DECIMAL(10,2) NULL`.
* **orders**: `id`, `store_id` FK, `buyer_id` FK, `total DECIMAL(10,2)`, `currency`, `status ENUM('pending','paid','packed','shipped','delivered','cancelled','refunded')`, `shipping_address JSON`, `billing_address JSON`, `tax_total DECIMAL(10,2)`, `shipping_total DECIMAL(10,2)`.
* **order_items**: `id`, `order_id` FK, `product_id` FK, `qty INT`, `unit_price DECIMAL(10,2)`, `tax_rate DECIMAL(5,2)`.
* **fulfillments**: `id`, `order_id` FK, `carrier`, `tracking_no`, `label_url`, `status ENUM('label_created','in_transit','delivered','returned')`.

### Acceptance

* End‑to‑end: create store → list product → place order → pay → generate label → track fulfillment.

---

## 1.9 Moderation & Safety

**Purpose:** Prevent abusive text and malicious files; central moderation queue.

### Tables & Fields

* **moderation_rules**: `id`, `type ENUM('bad_word','spam','malware','file_type')`, `pattern TEXT`, `action ENUM('block','quarantine','flag')`, `severity ENUM('low','medium','high')`.
* **moderation_flags**: `id`, `subject_type`, `subject_id`, `rule_id` FK, `status ENUM('open','approved','rejected')`, `notes TEXT`.
* **file_scans**: `id`, `media_id` FK, `engine ENUM('clamav','virus_total')`, `verdict ENUM('clean','suspicious','malicious')`, `report JSON`.

### Implementation Notes

* Upload pipeline: pre‑sign → quarantine → AV scan → publish. Text filters at API boundary; admin queue with SLAs.

### Acceptance

* Malicious file is quarantined; bad‑word post blocked/flagged; moderator actions audited.

---

## Migration Delivery Format

For each table above, deliver:

1. Laravel migration file(s) with FKs and indexes. 2) Eloquent model with `fillable`, casts (JSON/enum), relationships. 3) Factory + Seeder. 4) Policy (if user‑modifiable). 5) Unit tests for CRUD + constraints.

**Next Step (when you say go):** I’ll generate the first batch of actual Laravel migration stubs (jobs/bids/job_questions/job_events/media) and the corresponding Eloquent models and factories.

---

## 2) API Contracts (Representative)

> Prefix `/api/v1`. JWT/Sanctum auth. Cursor pagination. Consistent `data`, `meta`, `links`. Errors RFC7807.

### 2.1 Live Feed

* `GET /feed` — list feed items with filters: `near`, `radius_km`, `tags`, `budget_min/max`, `has_photos`, `status`.
* `GET /jobs/{id}` — job details + bids summary + Q&A.
* `POST /jobs` — create job (user). Payload: title, desc, photos[], location, budget_min/max, tags[]. Triggers `job_events.created`.
* `POST /jobs/{id}/bids` — place bid (provider). Payload: amount, message, attachments[].
* `POST /jobs/{id}/questions` — ask/comment. `POST /job_questions/{id}/answer`.
* WebSocket: `feed.updated`, `job.{id}.bid.placed`, `job.{id}.question.created`.

### 2.2 Escrow & Payments

* `POST /escrows` — fund escrow for job (payer = user).
* `POST /escrows/{id}/release` — full/partial: `{ amount }`.
* `POST /escrows/{id}/refund` — full/partial.
* Webhooks: `/webhooks/stripe`, `/webhooks/escrow`.

### 2.3 Disputes

* `POST /jobs/{id}/disputes` — open; `{ reason, claim_amount }`.
* `POST /disputes/{id}/message` — threaded; visibility.
* `POST /disputes/{id}/advance` — move stage (admin), with deadlines.
* `POST /disputes/{id}/settle` — agreements; auto‑calc payouts/refunds.

### 2.4 Search & Matching

* `GET /search` — unified endpoint `q`, `type=services|providers|jobs|products`, filters (price, rating, distance, availability, tags, delivery, stock).
* `GET /providers/{id}` — profile w/ ratings_agg, tags, portfolio.

### 2.5 Ads & Banners

* `GET /placements/{slot}` — returns fill list `{ creative, tracking_url }`.
* `POST /ads/{id}/impression` & `/click` — signed beacons.

### 2.6 Affiliates

* `GET /affiliates/me` — dashboard stats.
* `POST /affiliates/links` — create deep link with UTM.
* `GET /affiliates/payouts` — list & export.

### 2.7 Storefront

* `GET /stores/{slug}`; `GET /products` (filters: tag, price, stock, ship_to, delivery_days).
* `POST /orders` — create order; calculates tax, shipping, escrow if applicable for services.
* `POST /orders/{id}/pay` — Stripe etc.; `POST /fulfillments`.

---

## 3) Services & Domain Logic (Laravel)

### 3.1 Live Feed Service

* **Ingest:** On job create/update, write to `search_snapshots` and broadcast `FeedUpdated`.
* **Proximity:** Compute `h3_index` from lat/lng; query neighbors for fast geo radius. Fallback to MySQL `ST_Distance_Sphere` with spatial index.
* **Attachments:** All uploads → S3 with antivirus (ClamAV) pre‑sign + async scan; store blurhash.
* **Q&A & Bids:** Permission via policies. Providers can’t bid if blocked, expired, or owner.

### 3.2 Tax Engine

* Provider‑set tax profile(s); per‑jurisdiction rules. During checkout (escrow or product order):

  * Resolve nexus: `user.tax_profile` + `job.location` or `shipping_address`.
  * Apply VAT/GST or sales tax according to `scheme`. Multi‑rate support.
  * Persist `tax_total` and breakdown lines.

### 3.3 Escrow

* **Funding:** Charges payer including platform fee; move funds to held state. If Stripe, use PaymentIntents + separate `transfer_group`.
* **Release:** On completion acceptance or dispute resolution. Supports partial refunds; immutable `transactions`.
* **Timeouts:** Auto‑release after X days if no dispute (configurable). Cron task.

### 3.4 Disputes

* Stage workflow with deadlines (e.g., 3d negotiation, 5d mediation, 7d arbitration). Admin tools for evidence & settlement; auto payout math.

### 3.5 Unified Search

* Index docs for Jobs, Providers, Services, Products. Weighted fields: text, tags, distance, rating, completion rate, responsiveness.
* Query parser: one input box + advanced filters spec.

### 3.6 Ads/Banners

* Slot registry: `home_hero`, `feed_inline`, `search_card_top`, `sidebar`, `mobile_banner`. Fill by targeting (geo, tags, device). Frequency capping per user via Redis.

### 3.7 Affiliates

* Attribution via cookie + last‑touch header on app deep links. Track conversions when `escrow.released` or `order.paid`. Generate payout statements.

### 3.8 Storefront

* Providers create store, list products, manage inventory; shipping rules by zone; labels via Shippo/EasyPost (pluggable).
---

# Fixit + Taskup — **Mobile & UX Upgrade Deep Spec** (Sections 4–6 Only)

> This canvas **replaces** previous content and focuses *only* on the sections you provided. It is implementation‑ready for Flutter 3 (Dart 3) with Laravel APIs. Includes package layout, dependency list, routing, state, offline, realtime, native integrations, design tokens, accessibility, and measurable performance budgets.

---

# 4) Mobile Apps (Flutter) — Parity & UX

## 4.1 Architecture

**Goal:** Feature parity with web: Live Feed, Jobs/Bids/Q&A, Unified Search, Escrow/Payments, Disputes, Storefront, Affiliates, Profiles/Settings — all with offline tolerance and realtime updates.

### 4.1.1 Project Layout (Monorepo‑friendly)

```
apps/
  consumer_app/
  provider_app/
packages/
  core/            # shared: networking, auth, env, analytics, theme, i18n, storage
  auth/
  feed/
  search/
  jobs/
  bids/
  chat/
  payments/
  disputes/
  store/
  ads/
  profile/
  settings/
```

### 4.1.2 Core Dependencies

* **State**: `riverpod`, `flutter_hooks` (optional), `state_notifier` (lightweight controllers).
* **Networking**: `dio`, `retrofit`, `dio_cache_interceptor`, `pretty_dio_logger` (debug only), `chopper` (optional).
* **Serialization**: `json_serializable`, `freezed` for DTOs/unions.
* **Storage**: `hive` + `hive_flutter` (boxes: auth, user, feedCache, searchCache, settings), `shared_preferences` for light flags.
* **Realtime**: `pusher_channels_flutter` or `socket_io_client` (feature flag), `background_fetch`/`workmanager` for periodic sync.
* **Media**: `cached_network_image`, `photo_view`, `image_picker`, `file_picker`.
* **Maps/Geo**: `google_maps_flutter` or `flutter_map` (OSM), `geolocator`, `geocoding`, `h3_dart`.
* **Payments**: `stripe_sdk` (or `flutter_stripe`), deep links, Apple/Google Pay (later flag).
* **Push**: `firebase_messaging`, `firebase_core`, `flutter_local_notifications`.
* **Telemetry**: `sentry_flutter`, `firebase_analytics`, `device_info_plus`, `package_info_plus`.
* **Accessibility**: `flutter_svg` (vector icons with semantics), `flutter_animate` for non‑blocking motion.

### 4.1.3 App Bootstrap

* `core/bootstrap.dart` — initializes env (`.env.json` pulled at build), Sentry, Firebase, Hive boxes, theme, localization, and dependency graph (Riverpod Providers).
* `core/env.dart` — typed env (baseUrl, wsUrl, pusherKey, stripeKey, mapsProvider, feature flags).
* Enforce **strict null‑safety** and **fatal lints** (`pedantic`, `flutter_lints`).

### 4.1.4 State Model

* **Feature Providers**: one provider per use‑case (`FeedController`, `SearchController`, `JobController`, `BidController`, `DisputeController`, `StoreController`, `PaymentsController`, `AffiliateController`).
* **Immutable DTOs** with `freezed`; error union types `ApiError | NetworkError | ValidationError`.
* **Offline First**: write‑through cache; optimistic UI for bid placement/Q&A; background reconcile job for failures.

### 4.1.5 Networking & Interceptors

* Interceptors: `AuthInterceptor` (Bearer JWT), `RetryInterceptor` (exponential backoff, network reachability), `GzipInterceptor`, `TelemetryInterceptor` (X‑Request‑ID, app version, device info), `CachePolicyInterceptor` (60s SWR for GET feed/search).
* **Idempotency**: POSTs include `Idempotency-Key` (uuid v4). Store last keys for in‑flight retry safely.

### 4.1.6 Realtime

* Connect on `appResumed` or foreground; subscribe to `feed` and `job.{id}.*` channels; queue messages if app is backgrounded, surface notifications.
* Background isolate handles Pusher/Socket events → write into Hive → UI observers update.

### 4.1.7 Routing & Navigation

* Use `go_router` with deep link support. Guards check auth/KYC per route.
* Public routes: `/landing`, `/discover`, `/product/:id`, `/store/:slug`.
* Authed routes: `/feed`, `/jobs/:id`, `/checkout`, `/disputes/:id`, `/affiliate`, `/settings`.

---

## 4.2 Screens (Consumer & Provider variants where applicable)

### 4.2.1 Pre‑login Landing

**Purpose:** Marketing + conversion; shows read‑only nearby feed to entice signup.

* **Hero Carousel** (Lottie optional), USPs, CTA buttons (Sign in / Create account).
* **Discover Strip** (read‑only feed cards based on coarse location/IP); pressing any action prompts sign‑in.
* **Deep Links**: handle `fixit://job/:id`, `https://fixit.app/j/:id` to open Job Detail (prompt auth if needed).
* **Analytics**: `landing_view`, `cta_click`, `discover_scroll_depth`.

### 4.2.2 Feed

**Purpose:** Airtasker‑style discovery with fast filters.

* **Header**: search bar (single box), filter chip row (distance, price, category), map toggle.
* **Cards**: image (with blurhash placeholder), title, price range, distance/time‑ago, tags, mini‑avatars for bidders (count badge).
* **Filters Drawer**: distance slider (H3 radius), price min/max, tags, has photos, status, sort.
* **Map Toggle**: full‑screen map with clustered markers; tap to open JobCard preview.
* **Empty States**: suggest widening radius/price or enabling notifications.
* **Offline**: serve from `feedCache` with watermark; show refresh banner when online.

### 4.2.3 Job Detail

* **Header Gallery** with swipe; pinch zoom; media modal.
* **Details**: description (expandable), budget, location map (static until permission granted), owner profile.
* **Tabs**: `Bids`, `Q&A`, `Activity`.
* **CTA**: `Place Bid` (provider role) or `Edit/Close` (owner).
* **Q&A**: threaded; owner can mark an answer; avatars; relative timestamps.
* **Security**: attachments pass local heuristic (type/size) before upload; server AV gates final publish.

### 4.2.4 Search (Unified)

* **Single Search Bar** with type chips (All, Services, Providers, Jobs, Products).
* **Result Grid/List** automatically adjusts (2‑col on phones, 3‑4 on tablets).
* **Advanced Filters Modal**: price, rating, distance, availability calendar, tags, delivery days, stock availability.
* **Saved Searches**: star icon, optional push alerts for new matching jobs.

### 4.2.5 Escrow & Payments

* **Funding**: card entry via Stripe sheet; Apple/Google Pay flag; show tax lines from API; consent boxes (ToS, refund policy).
* **Status**: `Pending → Funded (Held) → Released/Refunded`; timeline with icons.
* **Receipts**: PDF via API link; share/export.
* **Errors**: decline codes surfaced with guidance; retry on network.

### 4.2.6 Disputes

* **Stage Tracker**: chips `Negotiation`, `Mediation`, `Arbitration`, `Closed` with deadline countdown.
* **Evidence Upload**: documents/photos (multi‑select), progress bar, server AV status pill.
* **Admin Chat**: separate tab; `admin_only` messages hidden from counter‑party.
* **Outcome**: settlement summary with payouts/refunds; exportable PDF.

### 4.2.7 Storefront

* **Product Grid**: image, price, stock status, delivery estimate.
* **Product Detail**: gallery, attributes, shipping calculator (ship‑to + rules), reviews (later).
* **Cart/Checkout**: address forms with postcode lookup (optional), shipping options, tax display, pay.
* **Orders**: status timeline; tracking deep links to carrier; return/dispute entrypoint.

### 4.2.8 Affiliate

* **Referral Link**: copy/share, QR code.
* **Stats**: clicks, signups, conversions, pending/paid; time range filters.
* **Channels**: create link with UTM; preview destination.

### 4.2.9 Settings

* **Account**: name, photo, email/phone verification, MFA setup.
* **Tax Profile**: create/edit profiles; set default; validate formats per country.
* **Zones**: manage preferred radius/areas; toggle location precision.
* **Notifications**: categories (feed nearby jobs, bid updates, dispute deadlines); quiet hours.
* **Payouts**: connect Stripe/Bank; view payout history.
* **Legal**: ToS/Privacy; data export/delete.

---

## 4.3 Native Integrations

### 4.3.1 Location Services

* **Permissions**: request with rationale; degrade gracefully (coarse IP → city centroid).
* **Throttling**: observe `geolocator` stream with debounce (min interval 30s foreground, 5m background); only recompute feed if H3 cell changes.
* **Geofencing**: optional: notify when high‑match jobs appear within X km; implemented with `workmanager` + lightweight pull or server push.

### 4.3.2 File Scanning

* **Client Heuristic**: block files > 25MB, disallow executables, validate MIME vs. extension.
* **Server AV**: uploads are pre‑signed → quarantine; UI shows `Scanning…` then `Ready`.

### 4.3.3 Push Notifications

* **Types**: `feed.new_nearby`, `bid.placed`, `dispute.deadline`, `order.status`, `payout.sent`.
* **Payload Schema** (example):

```json
{ "type": "bid.placed", "job_id": "job_...", "bid_id": "bid_...", "amount": 110 }
```

* **Handling**: background: show actionable notification; foreground: in‑app banner with CTA navigating to target screen via deep link.
* **Opt‑in**: explicit categories in Settings; quiet hours.

---

# 5) UI/UX Overhaul

## 5.1 Design Tokens & Theming

* **Typography**: Poppins or Inter; sizes: `xs 12`, `sm 14`, `base 16`, `lg 18`, `xl 20`, `2xl 24`, `3xl 30`.
* **Spacing**: 4‑pt grid; `xs 4`, `sm 8`, `md 12`, `lg 16`, `xl 24`, `2xl 32`.
* **Radius**: `lg` = 16px, `xl` = 20px, `2xl` = 24px (cards, modals, buttons).
* **Elevation**: soft shadows; dark mode elevation overlays.
* **Colors** (WCAG AA): primary, onPrimary, surface, onSurface, success, warning, error; map to Tailwind config for web parity.
* **Motion**: durations 120–220ms, use implicit animations; reduce motion if OS setting prefers reduced motion.

## 5.2 Component Library (Shared)

* **Cards**: `JobCard`, `ProviderCard`, `ProductCard` with shimmer skeletons and error placeholders.
* **Inputs**: accessible form fields with validation messages; large tap targets (48px min).
* **Buttons**: primary/secondary/tertiary; loading states; icon‑only with semantics.
* **Lists**: `SliverList`/`GridView` with `AutomaticKeepAliveClientMixin` where helpful.
* **Modals**: bottom sheets for filters and actions; draggable with safe‑area padding.

## 5.3 Landing Before Login

* Guard `/app/*` routes; public `/landing` → `/login` → `/app` after auth. Prevent back‑stack leak of protected screens.

## 5.4 Dashboards

* **Consumer**: Active jobs, messages, upcoming bookings, disputes, receipts.
* **Provider**: Leads (nearby open jobs), current jobs, earnings YTD, conversion funnel, store orders, payouts, affiliate tile.
* **Widgets**: modular; server‑driven layout possible later (feature flag).

## 5.5 Admin Redesign (Web reference; mobile read‑only views)

* Resource tables with quick filters, bulk actions, audit trail, impersonate; mobile apps expose read‑only summaries for provider/consumer where appropriate (e.g., dispute status).

## 5.6 Accessibility (WCAG 2.2 AA)

* **Contrast** ≥ 4.5:1 for text; test dark and light.
* **Semantics**: `Semantics` widgets; labels for icons; announce important state changes.
* **Keyboard**: hardware keyboard nav on tablets; focus traps in modals.
* **Text Scaling**: support up to 200%; auto‑wrap and expand.
* **Hit Areas**: min 48x48; ensure list rows and cards meet target size.

---

# 6) Performance Plan

## 6.1 Backend & Data

* **DB Indexes**: composite (`jobs(status,h3_index,created_at)`, `bids(job_id,status,created_at)`, `products(store_id,status)`, `disputes(job_id,stage,status)`).
* **Query Budgets**: p95 endpoint latency < 400ms; max 2 DB round‑trips per list call; N+1 checks via Laravel Telescope.

## 6.2 Caching

* **API**: Redis cache for feed/search windowed by H3 cell and filters (TTL 60s). Tag invalidation on job/bid changes.
* **Client**: Dio cache with SWR: serve cached first, then revalidate; LRU in Hive for 200 most recent items per list.

## 6.3 Assets & Images

* **Image Service**: Imgproxy/Thumbor to transform to WebP/AVIF; sizes: card (640w), thumbnail (320w), gallery (1280w).
* **Placeholders**: blurhash in JSON to instant paint.
* **Preload**: hint next images in list during scroll idle via `precacheImage`.

## 6.4 API Transport

* **Compression**: gzip; accept‑encoding header set by Dio; server sets `stale-while-revalidate` for hot reads.
* **DTOs**: concise fields; avoid over‑nesting; cursor pagination always.

## 6.5 Mobile Runtime

* **Paging**: `PagedListView` style with page size 10–20; prefetch +1 page when near end.
* **Jank Guard**: budget ≤ 8ms per frame work on mid‑range devices; avoid synchronous JSON work on UI thread — use isolates for heavy parsing.
* **Memory**: image cache cap ~128–192MB; dispose controllers on route pop.
* **Background Sync**: `workmanager` tasks short (<10s) and network‑aware; defer on low battery.

## 6.6 Observability & Quality Gates

* **Sentry**: crash‑free sessions target ≥ 99.5%.
* **ANR**: < 0.3% on Android; monitor via Play Console.
* **Web Vitals (Web)**: LCP < 2.5s, CLS < 0.1, INP < 200ms.
* **CI**: run Flutter analyze/test; golden tests for critical widgets; size budget (< 30MB APK split; < 80MB IPA release).

---

## Acceptance Criteria (Sections 4–6)

* **Parity**: Every web capability has a mobile path; e.g., creating a job appears on another device feed within ~1s over sockets.
* **Offline**: Feed/search usable without network (stale data flag); retries reconcile within 60s of reconnect.
* **Payments**: Escrow fund/release flows complete with receipts saved; errors surfaced clearly.
* **Disputes**: Stage tracker and deadlines accurate; evidence upload robust.
* **Storefront**: Browse → cart → checkout → track shipment; no oversell; taxes/shipping displayed.
* **Performance**: Scroll at 60fps on mid‑range devices; p95 API < 400ms; crash‑free ≥ 99.5%.
* **Accessibility**: All interactive elements meet 48px hit area; contrast AA; supports text scaling 200%.

---

> **Next**: I can generate **Flutter package scaffolds** (folder trees, pubspecs, Riverpod providers, Retrofit clients) for `feed`, `jobs`, and `payments` to start coding immediately.
---

# Fixit + Taskup — **Security, Admin/Ops, Ads** (Sections 7–9 Only)

> This canvas **replaces** previous content and focuses *only* on sections 7–9 you provided. It includes architecture, implementation steps, configs, policies, runbooks, and acceptance criteria. Stack: Laravel 10/11, MySQL 8, Redis, Stripe, S3, Cloudflare/Fastly, Flutter mobile apps.

---

# 7) Security & Compliance

**Goal:** Ship a platform that meets PCI SAQ‑A scope for payments, follows OWASP ASVS L2, and implements GDPR user rights, with end‑to‑end observability and automated security checks in CI/CD.

## 7.1 Authentication & Session Security

### 7.1.1 Passwordless Magic Links + OAuth

* **Magic Link Flow**

  1. User enters email → `POST /auth/magic-link` (rate‑limited). 2) Email contains single‑use link with short JWT (`aud=magic`, 10 min TTL) and CSRF token. 3) Clicking exchanges for long‑lived refresh token and short‑lived access token.
* **OAuth Providers**: Google, Apple; use PKCE on mobile; scope minimal (`email`, `profile`).
* **Implementation**

  * Controllers: `MagicLinkController@request`, `MagicLinkController@consume`.
  * Tokens: Laravel Sanctum (or Passport JWT). Access TTL 15m; Refresh TTL 30d; rotation on each use.
  * **Device Sessions**: `device_sessions` table (user_id, device_id, platform, ip, ua, last_seen, refresh_fingerprint). UI to revoke devices.
  * **Replay Prevention**: one‑time token store in Redis with 15m TTL; delete on consume.

### 7.1.2 MFA

* TOTP (RFC‑6238) via authenticator apps; backup codes (10 one‑use). Enforcement for admin & payouts.
* API: `/mfa/setup` (returns QR + secret), `/mfa/verify`, `/mfa/disable`.

### 7.1.3 Session Policies

* Access token rotation every 15m via silent refresh; refresh token rotates on use (store previous as invalidated with TTL).
* IP/Geo anomaly checks (new country → step‑up MFA).
* CSRF: same‑site `Lax` cookies for web; mobile uses Bearer.

## 7.2 RBAC & Authorization Policies

* **Roles**: `consumer`, `provider`, `both`, `admin`, `moderator`, `finance`.
* **Policies** (Laravel Policy classes): `JobPolicy`, `BidPolicy`, `EscrowPolicy`, `DisputePolicy`, `StorePolicy`, `AdPolicy`, `AffiliatePolicy`, `UserPolicy`.
* **Common Rules**

  * `JobPolicy@bid`: user has `provider` role, not owner, job `open`, KYC ok, limit 1 active bid.
  * `EscrowPolicy@release`: job completed or consumer acceptance; admin override with reason.
  * `DisputePolicy@advance`: admin or moderator only.
* **Attribute‑Based Access**: org/tenant optional later; use gates for feature flags (`can:use_storefront`).

## 7.3 Payments (PCI‑Aware)

* **Scope**: SAQ‑A by using Stripe Elements/SDK. No PAN touches servers; only Stripe tokens.
* **Server**: create PaymentIntent; collect client_secret on device; confirm on client.
* **Payouts**: Stripe Connect (standard or express) for providers. KYC flows enforced before withdrawals.
* **Webhooks Security**: verify signatures; idempotency keys for all state mutations.

## 7.4 File Security Pipeline

* **Upload Flow**: client requests pre‑signed S3 URL → uploads to `quarantine/` → server enqueues `ScanFileJob(media_id)`.
* **Scanning**: ClamAV (local or Lambda); fallback to VirusTotal (hash match) for large files.
* **Validation**: MIME sniff (server‑side), size cap (25MB default), type whitelist (jpeg/png/pdf/txt/webp/mp4 small), extension ↔ MIME match.
* **Outcomes**: `clean` → move to `public/` and publish; `suspicious/malicious` → keep quarantined and raise `moderation_flags`.
* **User Feedback**: UI shows `Scanning…` state; final status via WS or polling.

## 7.5 Content Protection & Abuse Controls

* **Bad‑Word Filters**: locale‑aware lexicons + regex; context window to reduce false positives; escalate to moderation queue if unsure.
* **Spam Throttles**: per‑route rate limits; greylisting for new accounts; recaptcha alternative (turnstile) on abuse.
* **Link Safety**: wrap outbound links via redirector `/r?u=<url>&sig=<hmac>`; Google Safe Browsing hash check; disallow IP literal links.
* **Chat Safety**: block phone/email leakage until job assigned (optional policy); detect repeated contact spam.

## 7.6 Secrets Management

* **Source of Truth**: AWS SSM Parameter Store (or Vault). CI pulls at deploy; no secrets in repo.
* **Rotation**: every 90 days for keys (`STRIPE_SECRET`, DB passwords, JWT signing). Use dual key windows for seamless rotation.
* **Templates**: `.env.example` with non‑sensitive placeholders; `docs/devops/secrets.md` for ownership and rotation runbook.

## 7.7 Application Security (AppSec)

* **SAST**: Larastan/PHPStan level max; PHP CS Fixer/Pint; Trivy/Grype for container images.
* **DAST**: OWASP ZAP weekly scan against staging; auth context configured; report gates (no High sev allowed).
* **Dependency Audit**: `composer audit`, `npm audit` fail build on high severity; Renovate bot for updates.
* **Security Headers**

  * CSP (report‑only → enforce): default‑src 'self'; img cdn + data:; media cdn; connect to API/WS; frame‑ancestors 'none'.
  * HSTS (1y includeSubDomains preload), X‑Frame‑Options DENY, X‑Content‑Type‑Options nosniff, Referrer‑Policy strict‑origin-when-cross-origin, Permissions‑Policy (geolocation, camera=()).
* **Rate Limiting**: default 120/min/user; sensitive `auth/*` 10/min; uploads 30/min; global IP ceiling.
* **Input Validation**: Laravel FormRequests + custom validators (geo bounds, tag slugs, currency ISO‑4217).

## 7.8 Privacy & Compliance (GDPR/UK‑GDPR)

* **User Rights**: export (`/privacy/export`) generating ZIP (JSON data, PDFs of invoices); delete (`/privacy/delete`) with 30‑day queue & legal holds for financial records.
* **Consent Management**: cookie banner + preferences (analytics/marketing) persisted per user/device; CMP integration ready.
* **Data Minimization**: only required PII stored; redact PII in logs by default.
* **Audit Logging**: append‑only `audit_logs` table (who, what, when, where (IP/UA), before/after hash); immutable via WORM S3 export nightly.
* **Retention**

  * Auth logs: 1y; Audit logs: 2y; Financial records: 7y; Chat/media: per account until deletion; Backups: 30–90d.
* **DPA & Subprocessors**: maintain registry (Stripe, AWS, Cloudflare, Sentry, Shippo, Meilisearch). Publish list in privacy policy.

## 7.9 Security Runbooks & Incident Response

* **Playbooks**: account takeover, payment dispute fraud, data exposure, secret leak, malware upload.
* **On‑Call**: rotation calendar; severity matrix (SEV‑1 to SEV‑4) with response SLAs.
* **Forensics**: centralized logs, immutable audit, database point‑in‑time recovery enabled.

### 7.10 Acceptance Criteria (Security)

* Penetration test (staging) shows **no High/Critical**; CSP enforced without breakage.
* Webhooks signature verification mandatory; replay attempts rejected.
* Magic link and OAuth flows pass automated E2E; MFA enforced for admin/payouts.
* File uploads quarantined and scanned; malicious samples blocked in tests.
* GDPR export/delete flows verified; audit logs immutable and queryable.

---

# 8) Admin & Ops Tooling

## 8.1 Admin Modules (Laravel Nova/Filament/Custom)

* **Dispute Console**: list by stage/status; timers and SLA badges; actions: advance stage, request evidence, propose settlement, finalize payout/refund.
* **Escrow Ledger**: per job/provider; filter by status; drill into transactions; export CSV; reconcile vs Stripe.
* **Tax Profiles**: CRUD for provider tax profiles; validation helpers; bulk update; impersonation to view invoices.
* **Zone Editor**: map with draw tools (Leaflet); save polygons; snap to admin boundaries; H3 resolution picker; validate self‑intersections.
* **Ad Manager**: campaigns, creatives, targeting (geo, tags, device, role), budgets (CPC/CPM), pacing controls, preview in slot mocks.
* **Affiliate Manager**: affiliate onboarding, link generator, attribution viewer, payout statements creation and export.
* **Store Moderation**: queue for new products, policy checks, image review, takedown/restore with reason templates.
* **Content Flags Queue**: triage bad‑word/abuse, escalate to moderator; SLA timers.
* **User Admin**: KYC/KYB status, device sessions, ban/suspend with durations and reasons.

## 8.2 Ops & Observability

* **Error Tracking**: Sentry projects (web, iOS, Android); release health; deploy markers.
* **App Diagnostics**: Laravel Telescope (dev/stage), Horizon (queues), Queue length alerts.
* **Metrics/APM**: Prometheus exporters (PHP‑FPM, Nginx, Redis, MySQL, Horizon); Grafana dashboards.
* **Logs**: JSON logs → Loki/ELK; request IDs; PII redaction.
* **Tracing**: OpenTelemetry optional; propagate `traceparent` to downstreams.
* **Backups**: nightly DB snapshot; S3 versioning; disaster recovery docs and restore drills quarterly.

## 8.3 Alerting & SLOs

* **SLOs**: API uptime 99.9%; p95 latency < 400ms; crash‑free mobile sessions ≥ 99.5%; websocket deliver ≤ 1s p95.
* **Alerts**: error rate spike, payment failure rate > 2%, queue lag > 2m, WS disconnect storm, DB replica lag > 30s.

## 8.4 Analytics (Product & Growth)

* **Event Bus**: server emits structured events to Kafka (or Redis Stream) then sinks to BigQuery/ClickHouse.
* **Core Events**

  * `feed_view`, `job_view`, `bid_place`, `bid_accept`, `escrow_fund`, `escrow_release`, `dispute_open`, `dispute_settle`, `ad_impression`, `ad_click`, `affiliate_convert`, `store_order_paid`.
* **Event Schema (example)**

```json
{
  "event": "bid_place",
  "ts": "2025-09-29T10:00:00Z",
  "user_id": "usr_...",
  "job_id": "job_...",
  "amount": 120,
  "device": {"os":"iOS","app":"consumer"},
  "geo_h3": 599537...
}
```

* **Governance**: schema registry; version events; PII minimization; consent checks before marketing sinks.

### 8.5 Acceptance Criteria (Admin & Ops)

* Admin can progress dispute, settle, and see escrow ledger reflect payouts in real time.
* Zone editor saves valid polygons and affects search/feed results after cache invalidation.
* Ads and affiliates managers can create campaigns/links and see metrics within 15 minutes.
* Observability dashboards show golden signals; alerts tested.

---

# 9) Ads/Banners Upgrades

**Goal:** Deliver a privacy‑aware, high‑performance ad system with clear targeting controls, frequency capping, fraud resistance, and unified rendering across web/mobile.

## 9.1 Slot Registry & Client Rendering

* **Slots**: `home_hero`, `feed_inline`, `search_card_top`, `sidebar`, `mobile_banner`, `checkout_sidebar`.
* **Registry** (server): defines dimensions, allowed types, pacing rules, default fallbacks.
* **Client Components**

  * Web: `<AdSlot slot="feed_inline" />` lazy‑loads; renders skeleton until fill arrives.
  * Mobile: `AdSlotWidget(slot)` with aspect‑ratio constraints and click handlers.

## 9.2 Fill Service & Waterfall

* Endpoint: `GET /placements/{slot}` returns ordered list of eligible creatives.
* **Targeting**: geo (country/zone/H3), role, device, tags, page context; exclude sensitive categories.
* **Pacing & Rotation**: weighted round‑robin with per‑campaign caps (daily, total). Redis counters per campaign & slot.
* **Frequency Capping**: `cap:{user}:{slot}:{date}` with counts; default max 5/day/slot/user.

## 9.3 Creative Specifications & Validation

* **Types**: `banner` (static image), `card` (title+image+cta), `feed_promo` (native card style).
* **Assets**: PNG/JPG/WebP/AVIF; max 500KB preferred; aspect ratios per slot; mandatory alt text.
* **Validation**: server checks size/type; runs image optimization (Imgproxy) to WebP/AVIF; stores dominant color for placeholder.
* **Click Safety**: landing URLs pass through redirector with HMAC; UTM auto‑appended.

## 9.4 Measurement & Anti‑Fraud

* **Impressions**: POST beacon on viewable impression (IntersectionObserver/web; viewport threshold on mobile). Debounce to avoid scroll jitter.
* **Clicks**: signed endpoint; dedupe rapid repeats; referer/origin checks; bot heuristics (headless signals, speed anomalies).
* **Attribution**: last‑touch (cookie 30–90d); respects consent prefs; no cross‑site tracking.

## 9.5 Privacy & Consent

* **CMP Integration**: only load marketing beacons when user consents; store preferences per device.
* **Data Minimization**: ad requests include coarse geo (H3) and context tags; never raw GPS unless explicit.

## 9.6 Performance Budgets

* **Web**: ad JS ≤ 20KB gzip per page; load async; no layout shift (reserve space with CSS aspect‑ratio); images lazy.
* **Mobile**: ad payloads ≤ 5KB metadata + 1 image; prefetch next ad during idle.

## 9.7 Admin UX for Ads

* Create campaign → upload creatives → set targeting & budgets → preview in slot mocks → start/pause → monitor CTR/eCPM.
* Reports: by slot, campaign, device, geo; export CSV.

### 9.8 Acceptance Criteria (Ads)

* Slots fill within 200ms server time; no CLS>0.02 from ad loads.
* Frequency capping respected across sessions/devices (if logged in); anonymous capping via local storage.
* Impression/click logs reconcile within ±2% to analytics sink.
* Creatives optimized automatically; invalid assets rejected with clear errors.

---

> **Next**: I can generate **policy stubs, middleware, and config snippets** (CSP headers, rate limits, auth routes), plus admin panel scaffolds for Dispute Console, Escrow Ledger, Zone Editor, and Ad Manager.

---

# Fixit + Taskup — **Affiliates, Zones/Geo, Search Ranking** (Sections 10–12 Only)

> This canvas **replaces** previous content and focuses *only* on sections 10–12 you provided. It is implementation‑ready: data flows, APIs, cron jobs, formulas, UI, and acceptance tests. Assumes prior schemas from Section 1 (affiliates, zone_polygons, ratings_agg, search_snapshots).

---

# 10) Affiliate Program (Extensive)

**Goal:** Acquire users via tracked referrals; attribute conversions to affiliates; compute commissions; pay out monthly via Stripe Connect or bank. Support tiers, cookie windows, and first/last‑touch models.

## 10.1 Concepts & Entities

* **Affiliate**: registered user with a unique `code` (e.g., `ORBX123`). Has a **tier** and **rate_pct** (default 5%).
* **Link**: deep link with channel/slug, e.g., `/r/ORBX123/instagram?dest=/landing&utm[campaign]=q4`. Redirector sets cookies & UTM.
* **Attribution**: ties a visitor/user to an affiliate with timestamps for `first_touch` and `last_touch`.
* **Conversion**: event that yields commission (`escrow.released` for services, `order.paid` for products).
* **Payout**: monthly aggregate of approved commissions.

## 10.2 Tracking & Attribution Flow

1. **Click** → User visits `GET /r/{code}/{channel?}?dest=...&utm[...]`. Server validates code, writes `affiliate_attributions` cookie **and** DB record (`first_touch_at` if absent, always updates `last_touch_at`), sets `affiliate_code`, `channel`, UTM in cookie with expiry **cookie_days** (30–90d). For guests, store visitor id in cookie.
2. **Signup/Login** → If cookie exists, attach attribution to the created/logged‑in user (`affiliate_attributions.user_id`). If another affiliate click occurs before signup, model determines attribution: **last‑touch** (default) or **first‑touch** (configurable per campaign).
3. **Conversion** → On `escrow.released` or `order.paid`, emit `affiliate_convert` with `amount`, `currency`, `user_id`, `affiliate_id`, `order_id|job_id`. Compute commission = `amount * rate_pct` (apply tier overrides, min/max caps per campaign optional). Record provisional ledger row for the payout period.

### Cookies & Server Storage

* Cookie name: `aff_src` (JSON: `{ code, channel, utm, set_at }`) with `Max-Age = cookie_days * 24h`, `SameSite=Lax`, `Secure`.
* DB: `affiliate_attributions` keeps durable mapping; update `last_touch_at` each qualified click.

## 10.3 Tiers & Rates

* **Default**: 5% (configurable). Tiers: `bronze 5%`, `silver 7%`, `gold 10%`, `partner custom`. Admin may override per affiliate or per campaign.
* **Advanced**: allow **floor/ceiling** on commission per conversion (e.g., min £1, max £100). Currency converted to affiliate’s payout currency using daily FX cache.

## 10.4 Payout Cycle (Monthly)

* **Period**: calendar month in site timezone.
* **Lock Window**: conversions finalized T+7 days to allow refunds/chargebacks. Ledger rows within the month but not finalized are deferred.
* **Generate**: `artisan affiliates:payouts --period=2025-09` aggregates by affiliate: sum approved commissions; write `affiliate_payouts` with status `pending`.
* **Export/Pay**:

  * **Stripe Connect**: create transfers to the connected account with descriptor `Affiliate Sep 2025`.
  * **Bank**: generate CSV with columns: `affiliate_id,name,iban,amount,currency,period`.
* **Reconciliation**: webhook or manual confirm → mark `paid`, store evidence JSON (transfer IDs / bank file hash).

## 10.5 Fraud & Quality Controls

* Block self‑referrals (same user/device). Filter clicks from flagged IP ASN/bots; debounce repeated clicks (<5s) per user.
* Post‑signup invalidation: if user already had a different affiliate before (and model is first‑touch), ignore later touches.
* Refund clawbacks: if an escrow or order is refunded after payout, deduct from next period (negative adjustment row).

## 10.6 API & Events

* `GET /affiliates/me` — returns code, rate_pct, tier, summary stats (`clicks`, `signups`, `conversions`, `pending`, `paid`).
* `POST /affiliates/links` — create deep link with channel & UTM; returns share URL and QR.
* `GET /affiliates/payouts?status=...&period=...` — list historical payouts.
* **Events**: `affiliate_click`, `affiliate_signup`, `affiliate_convert`, `affiliate_payout_created`, `affiliate_payout_paid` (sink to analytics).

## 10.7 Affiliate Dashboard (Web & Mobile)

* **KPIs**: Today/7d/30d clicks, signups, conversions; current period earnings (pending/approved); lifetime paid.
* **Link Builder**: choose destination path; add UTM & channel; copy/share; QR code generator.
* **Charts**: time series for clicks→signups→conversions funnel.
* **Statements**: monthly PDFs (line items) with payout evidence.

## 10.8 Admin Controls

* CRUD affiliates, set `tier`, `rate_pct`, `cookie_days`, `payout_method` (Stripe Connect account id or bank details).
* Attribution viewer: per user shows first/last‑touch and click history.
* Payout run tool with dry‑run diff; export CSV; post‑run reconciliation and adjustments.

### 10.9 Acceptance (Affiliates)

* Click → signup → conversion paths attribute correctly under both models; cookies expire as configured.
* Monthly payout file/transfers match aggregate within ±0.01 rounding; refunds create clawback rows.
* Dashboard KPIs match analytics sink; admin can override rates and regenerate a period safely.

---

# 11) Zones & Location Logic

**Goal:** Fast, precise geo logic for feed, search, shipping, and tax. Normalize coordinates, compute H3 indices, and support polygonal zones with spatial queries.

## 11.1 Coordinate & SRID Standards

* Store all lat/lng as **WGS84 SRID 4326** in MySQL `POINT` columns.
* Add **SPATIAL INDEX** on relevant tables (`jobs.geo_point`, `zone_polygons.polygon`).
* Store **H3 index** (res 8 default; 9 for dense cities) in BIGINT columns to accelerate proximity windows.

## 11.2 H3 Computation & Usage

* On create/update of any geo entity (job, store shipping rule, user zone), compute `h3_index = h3.geoToH3(lat,lng,res)`.
* **Radius Queries**: approximate by querying target cell + neighbors (`kRing`) before precise distance check (`ST_Distance_Sphere`).
* **Cache**: hot H3 windows cached in Redis (60s) for feed & search.

## 11.3 Zone Matching

* **Polygon Method**: `SELECT ... WHERE ST_Contains(zone_polygons.polygon, jobs.geo_point)` for authoritative zone rules (tax/shipping).
* **H3 Containment**: pre‑compute cell sets for large polygons when speed is critical; store in `meta` as compressed list; use set membership for quick filter, then verify with polygon.

## 11.4 Backfill & Data Hygiene

* Background job `BackfillGeoJob` scans legacy rows without `geo_point` or `h3_index`:

  1. Parse any stored address → geocode (components first).
  2. Fill `geo_point` and `h3_index`.
  3. Update `search_snapshots`.
* Run nightly geocoding quota; mark failures with reason.

## 11.5 Geocoding Strategy

* **Components‑first**: try `{ country, postal_code, city, street }` to reduce ambiguity; cache by checksum (`sha256` of normalized components) in `geo_cache` with TTL (30–90d).
* Prefer provider that returns **rooftop** or **route** level; fall back to centroid.
* Reverse geocoding used for display only; never overwrite stored coordinates unless user confirms.

## 11.6 Permissions & Precision

* Respect user location permissions. When denied, use coarse IP‑based centroid; label results as approximate. Allow users to set **preferred zones** manually (saved shapes or radius).

### 11.7 Acceptance (Zones/Geo)

* Feed and search return results within radius tolerance (±3% versus great‑circle ground truth) at p95 latency < 300ms for cached windows.
* Polygon rules (e.g., city zone) correctly include/exclude jobs and compute taxes/shipping accordingly.
* Backfill populates ≥ 99% of legacy rows; failures are logged with actionable reasons.

---

# 12) Search Algorithm & Ranking

**Goal:** Single ranking function across entities (providers, services, jobs, products) that balances relevance, proximity, quality, timeliness, and price fit. Tunable weights; offline evaluation with golden sets. LTR (learning‑to‑rank) optional later.

## 12.1 Index & Features

* **Indexed Fields** (by entity):

  * **Job**: title, description (stemmed), tags, budget_min/max, currency, geo (h3), created_at, status.
  * **Provider**: name, bio, tags/skills, service areas (h3 set), `ratings_agg` (avg, jobs_done, on_time_pct), response_time_ms, price_band.
  * **Product**: name, desc, tags, price, stock, shipping zones, delivery_days.
* **Derived Features**

  * `distance_km` (query point → entity point)
  * `price_fit` (1 − |target_mid − candidate_mid| / max(target_mid,1)) if user provided a price intent.
  * `recency_score = exp(-age_days / τ)`; τ default 14.
  * `availability_score` (provider availability calendar overlap or product stock/delivery_days).
  * `tag_match` (Jaccard index between query tags and entity tags).

## 12.2 Scoring Function (Default Weights)

Let normalized features in [0,1]. Default weights:

* `w_text=0.40`, `w_distance=0.20`, `w_rating=0.12`, `w_completion=0.06`, `w_response=0.05`, `w_price=0.07`, `w_recency=0.07`, `w_availability=0.03`, `w_tag=0.00..0.10` (auto‑increase when user selects tags).

**Provider Score**

```
score_provider =
  w_text * text_rel +
  w_distance * (1 - min(distance_km/target_radius, 1)) +
  w_rating * (avg_rating/5) +
  w_completion * (jobs_done_norm * on_time_pct) +
  w_response * (1 - response_time_norm) +
  w_price * price_fit +
  w_recency * recency_score +
  w_availability * availability_score +
  w_tag * tag_match
```

**Job Score**

```
score_job = w_text*text_rel + w_distance*geo_prox + w_price*price_fit + w_recency*recency_score + w_tag*tag_match
```

**Product Score** follows similar but replaces distance with delivery_days_fit.

## 12.3 Normalization

* `jobs_done_norm = min(jobs_done / 100, 1)`; `response_time_norm = clamp(log1p(ms)/log1p(86400000), 0, 1)`.
* `geo_prox = 1 - min(distance_km / radius_km, 1)`.

## 12.4 Query Parsing

* One text box → tokens → spell‑correct → tag extraction (dictionary of known services/tools) → geo intent (e.g., “near SE1” → set `near`, `radius_km`).
* If user chooses entity type, restrict index; else blended results with per‑type quotas (e.g., 50% providers, 30% jobs, 20% products on first page, adaptive based on click feedback).

## 12.5 Personalization (Optional, Privacy‑aware)

* Re‑rank using user’s historical clicks/conversions within same tag categories (epsilon‑greedy; cap influence at 20%).
* Never leak cross‑user data; per‑user vectors stored server‑side with consent.

## 12.6 Feedback & Analytics

* Log impressions and clicks with feature snapshot & score to evaluate A/B changes.
* Maintain **golden query set** of ~200 queries with expected top results and measure NDCG@k.

## 12.7 Performance

* Use Meilisearch for text + attribute filters; distance pre‑filter via H3 window then fine sort in app.
* p95 blended search under 300ms (cached hot windows) / 600ms cold.

### 12.8 Acceptance (Search)

* Top‑k quality: NDCG@10 ≥ 0.85 on golden set; CTR improves ≥ 10% vs baseline after deploying unified ranking.
* Response p95 ≤ 600ms cold & ≤ 300ms warm; relevancy stable across locales.
* Ranking weights hot‑swappable via admin config; rollback to previous profile in one click.

---

> **Next**: I can generate concrete code stubs — affiliate redirect controller + cookie logic, payout aggregation command, H3 utility service, and a Search `QueryService` with the scoring function and tests.

---

## 13) Dispute System — Flow

1. **Negotiation** (parties converse; templates for partial refunds). 2) **Mediation** (admin helps; proposes settlement). 3) **Arbitration** (admin decision; bind). Each stage with SLA timers & auto‑progress. Evidence upload, redaction, and visibility controls.

---

## 14) Payments & Escrow Scenarios

* Single‑milestone (default): full hold → complete → release → affiliate cut.
* Multi‑milestone (optional): staged holds. Partial refunds compute net of fees based on policy.

---

## 15) Storefront (Provider Tools) — eCommerce

* Catalog mgmt, variants (size/color), stock, shipping zones, taxes, coupons. Orders lifecycle: created → paid → packed → shipped → delivered → return/dispute.
* Integrations: Shippo/EasyPost adapters; webhooks for label status.

---

## 16) Moderation, Bad‑Word & File Scanning

* Lexicons per locale; regex + DFA; context filters to reduce false positives. Files go quarantine → AV scan → safe → publish. Admin flag queue with SLAs.

---

## 17) Speed Upgrades

* Replace heavy carousels; defer non‑critical JS; split vendor bundles; HTTP caching; DB read replicas (later). Use ETags for media.
---

# Fixit + Taskup — **Mobile Redesign, CI/CD, Admin UX, Testing, Migrations, Analytics** (Sections 18–23 Only)

> This canvas **replaces** previous content and focuses *only* on the sections you provided. It is implementation‑ready with component lists, pipeline YAML guidance, wireframes, test matrices, migration runbooks, and KPI definitions.

---

# 18) Mobile App Redesign Notes

**Goal:** Make the consumer and provider apps feel fast, modern, and intuitive. Emphasize clarity, offline resilience, and discoverability while keeping animations cheap.

## 18.1 Navigation & Layout

* **Root**: Bottom navigation (5 tabs max) + **center FAB**.

  * **Consumer** tabs: `Feed`, `Search`, `Post`, `Messages`, `Profile` (FAB launches `Post Job`).
  * **Provider** tabs: `Leads` (feed filtered to open nearby jobs), `Calendar` (availability), `Messages`, `Earnings`, `Profile` (FAB launches `Quick Bid` or `Create Product`).
* **Nested Stacks** per tab using `go_router` with `ShellRoute` to preserve state.
* **Map Mini‑Cards**: on `Feed` and `Leads`, a sticky mini‑map (collapsible) with clustered markers; tapping a marker shows a **mini‑card** overlay with title, price, CTA.

## 18.2 Dark Mode

* System‑driven (follows OS). Theme tokens prepared for semantic colors: `surface`, `surfaceElevated`, `textPrimary`, `textSecondary`, `accent`, `warning`, `success`.
* Ensure WCAG AA contrast at 4.5:1; test on OLED (true black in modals, not full surfaces to avoid banding).

## 18.3 Motion & Micro‑interactions

* Use **implicit animations** only (`AnimatedContainer`, `AnimatedOpacity`, `AnimatedSwitcher`).
* Durations 120–200ms; curve `fastOutSlowIn`. Respect `reducedMotion` OS setting.
* Examples: card hover/tap, filter chips expansion, map mini‑card reveal.

## 18.4 Skeleton Loaders & Empty States

* **Skeletons**: JobCard/ProviderCard/ProductCard have shimmer placeholders (title lines, image block, meta chips). Ensure skeleton heights match final layout to prevent CLS.
* **Empty States**: friendly illustrations + actions ("Widen radius", "Enable notifications", "Try another tag").

## 18.5 Offline UX

* **Offline Toasts**: non‑blocking banner: "You’re offline — showing saved results" with retry button.
* **Write Operations**: queue bid/Q&A messages for retry; mark as `Pending…` with clock icon and disable duplicate actions.
* **Images**: show blurhash + cached thumbnail; defer full‑res until online.

## 18.6 Accessibility & Internationalization

* 48px minimum touch targets; dynamic type up to 200%; high‑contrast focus rings.
* RTL mirrored layouts; localized date/time/currency; pluralization rules.

## 18.7 Quality Bars

* **Crash‑free sessions** ≥ 99.5%; **start time** < 1.5s cold; **scroll jank** < 1% frames >16ms on mid‑range devices.

---

# 19) CI/CD & Environments

**Goal:** Safe, automated builds and deployments for web/admin and both mobile apps with environment isolation and fast feedback.

## 19.1 Branching & Versioning

* Branches: `main` (prod), `develop` (staging), `feature/*`.
* **Semantic Versioning** for backend (`vX.Y.Z` tags). Mobile uses **calver + build number** (e.g., `2025.09.29 (120)`).
* Protected branches: require PR, status checks, code review, security scan gate.

## 19.2 GitHub Actions (suggested jobs)

* **backend-ci.yml**

  * Triggers: PR to `develop`/`main`.
  * Steps: setup PHP; cache composer; `composer install --no-dev` for prod build; `phpstan`, `pint --test`, `phpunit --coverage`; `composer audit`.
  * Artifacts: built assets via Vite, OpenAPI JSON, coverage report.
* **backend-dast.yml**

  * Triggers: nightly on `develop` env URL.
  * Steps: OWASP ZAP baseline scan; upload SARIF; fail on High.
* **frontend-ci.yml** (if SPA): `npm ci`, `eslint`, `tsc --noEmit`, `vitest`, `vite build`.
* **mobile-ci.yml**

  * Matrix: iOS/Android.
  * Steps: Flutter `pub get`, `analyze`, `test`; build with **fastlane** lanes `beta_ios`, `beta_android`.
  * Artifacts: `.ipa` and `.aab` uploads to TestFlight/Play Internal.

## 19.3 Deployments

* **Web/Admin**: container image build → push to registry → deploy to staging via GitOps (ArgoCD) or Helm; run `php artisan migrate --force` with health checks; blue/green or rolling.
* **Mobile**: fastlane handles signing; submit to TestFlight/Play (internal → closed → production tracks).
* **Feature Flags**: backend `features` table + env flags to gate new modules; mobile uses remote config.

## 19.4 Environment Configuration

* Envs: `local`, `staging`, `production`. Secrets from SSM/Vault; no secrets in repo.
* Shared **env template**:

```env
APP_ENV=production
APP_URL=https://fixit.example.com
DB_*
REDIS_*
BROADCAST_DRIVER=pusher
PUSHER_APP_*=
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=
STRIPE_KEY=
STRIPE_SECRET=
ESCROW_PROVIDER=stripe
MAPS_PROVIDER=google
GOOGLE_MAPS_KEY=
IMG_PROXY_URL=
CSP_ENABLED=true
```

* Additional namespaces: `PAYMENTS_*`, `ESCROW_*`, `GEOCODER_*`, `SEARCH_*`, `ADS_*`, `AFFILIATE_*`, `SHIP_*`, `SENTRY_*`, `FIREBASE_*`.

## 19.5 Release Management & Rollback

* **Migrations**: add-only, backward compatible; dangerous changes gated by feature flag + two‑phase rollout.
* **DB Backups** before deploy; **schema diff** review in PR.
* **Rollback**: revert image tag; run down migrations only for non‑destructive changes; otherwise feature-flag off and forward‑fix.

## 19.6 Observability in CI/CD

* Post‑deploy smoke tests (synthetic checks) hit `/health`, `/api/v1/feed`, `/api/v1/search`.
* Auto‑annotate Sentry releases and Grafana dashboards with deploy markers.

---

# 20) Admin UX Wireframe (Text)

**Goal:** Provide powerful, safe admin tooling with consistent patterns.

## 20.1 Global Layout

* **Left Nav** (fixed): Dashboard, Feed, Jobs, Bids, Disputes, Escrows, Taxes, Zones, Stores, Products, Orders, Ads, Affiliates, Moderation, Users, Settings, Logs.
* **Top Bar**: search, environment badge, quick actions (impersonate, create campaign), user menu.

## 20.2 List Views

* **Table Patterns**: sticky header; columns: key fields, status, owner/provider, created_at; row overflow menu.
* **Controls**: quick filters (chips), advanced filter panel (date range, status, tag, geo), **saved views** (per‑user), export CSV.
* **Bulk Actions**: approve/reject, pause/resume, assign, advance stage, tag.
* **Row Drawer**: slide‑out with details, audit trail, quick edit.

## 20.3 Detail Views

* **Dispute**: stage tracker, messages (party/admin), evidence gallery, settlement composer with math, timers.
* **Escrow**: ledger (transactions), balances, webhooks log, actions (release/refund) with MFA.
* **Zone Editor**: map with draw/snap tools, polygon list, validation messages, H3 overlay.
* **Ads**: campaign overview, pacing chart, creatives preview, targeting form with live eligibility count.

## 20.4 Safety & Audit

* Confirmations with reason codes for sensitive actions; MFA prompts for payouts, bans, and arbitration decisions.
* Per‑row **audit trail** with diff views; export audit as CSV.

---

# 21) Testing Strategy

**Goal:** Ship with confidence using a pragmatic test pyramid.

## 21.1 Unit Tests

* **Services**: TaxEngine (jurisdictions, rounding), EscrowService (fund/release/refund), Search scoring function, DisputeService transitions.
* **Policies**: authorization allows/denies edge cases; data providers for roles.
* **Utilities**: H3 conversions, geocoding caches, validators.

## 21.2 Integration/API Tests

* Endpoint tests for `/feed`, `/jobs`, `/escrows`, `/disputes`, `/search`, `/placements`, `/affiliates/*`, `/orders`.
* Webhooks: Stripe (success/refund), Shippo/EasyPost status updates; signature verification and idempotency.
* File pipeline: pre‑sign → upload → AV scan → publish paths.

## 21.3 End‑to‑End

* **Web (Cypress)**: happy paths: create job → see in feed; place bid; fund escrow; dispute & settle.
* **Mobile**: Flutter integration tests (goldens for cards; flows for login, feed scroll, bid placement, checkout, dispute tracker).

## 21.4 Performance & Security

* **k6**: load `/feed`, `/search`, `/placements` with realistic traffic patterns; assert p95 < budget.
* **ZAP**: baseline weekly; regression gate on High severities.
* **Fuzz**: quickcheck critical parsers (search query, geo input).

## 21.5 Data & Fixtures

* Seeders create 100+ jobs with geo spread, providers, affiliates, stores/products, ads.
* VCR-style HTTP snapshots for third‑party APIs (Stripe test keys, fake webhooks) to stabilize tests.

## 21.6 Coverage & Gates

* Backend statements ≥ 80%; critical services ≥ 90%.
* Flaky test quarantine label; build fails if >2% flakiness.

## 21.7 UAT Scripts

* Role‑based checklists (consumer/provider/admin) with step‑by‑step verification of KPIs and edge cases.

---

# 22) Migration & Backfill Plan

**Goal:** Zero‑surprise rollout of new schemas and search/geo backfills with safe rollback.

## 22.1 Pre‑flight

* Freeze schema changes week of deploy; confirm backups; run staging rehearsal with production‑sized snapshot.
* Generate migration order; ensure add‑only or online (no long table locks); prepare feature flags.

## 22.2 Execution Steps

1. Enter maintenance (read‑only) if needed for brief window.
2. Run `php artisan migrate --force`.
3. Queue **Backfill H3** for all jobs/users needing `h3_index`.
4. Rebuild `search_snapshots` and reindex Meilisearch.
5. Warm caches (feed/search windows) and pre‑render landing.
6. Flip feature flags to enable new modules gradually.
7. Exit maintenance; monitor dashboards and logs.

## 22.3 Rollback Plan

* If read path broken: toggle feature flag off.
* If schema causes errors: deploy previous image; avoid destructive down migrations; use compatibility layer until forward fix.
* Keep append‑only logs intact for forensics.

## 22.4 Post‑Deploy Validation

* Synthetic checks pass; error rate within baseline; queue lag < 1m; search returning expected counts; geo queries under p95 budget.

---

# 23) Analytics & KPIs

**Goal:** Clear metrics to steer growth, marketplace health, and revenue.

## 23.1 North Star & Marketplace Health

* **North Star**: Successful service completions per active user (consumer & provider sides normalized).
* **Liquidity**: time‑to‑first‑bid, bids per job, bid acceptance rate.

## 23.2 Growth & Engagement

* **Feed CTR** = clicks on job cards / feed impressions.
* **Search → Contact Rate** = job detail opens with bid intent / search result impressions.
* **Activation**: percentage of new providers placing first bid within 48h; consumers creating first job within 24h.

## 23.3 Monetization

* **Escrow Funded Rate** = escrows funded / open jobs.
* **Take Rate** = (platform fees + affiliate net) / GMV.
* **Ad eCPM** = (ad revenue / impressions) * 1000.
* **Affiliate ROI** = (revenue from affiliate conversions – payouts) / payouts.
* **Store AOV** = average order value; **Refund Rate** = refunded orders / paid orders.

## 23.4 Retention & Churn

* Cohort retention (W1, W4, W8) for consumers and providers; provider 90‑day churn.
* **LTV** (rolling 12‑month revenue per retained user) with survival‑adjusted estimate.

## 23.5 Instrumentation & Dashboards

* Emit analytics events from server and apps with consistent schema (see prior sections).
* Dashboards: `Acquisition`, `Activation`, `Liquidity`, `Quality`, `Monetization`, `Support`.
* Alerts: sudden drops >20% in liquidity or spikes in dispute rate >2x baseline.

## 23.6 Experimentation

* A/B framework: flag variants, randomization by user id, exposure logging, guardrail metrics (dispute rate, refund rate, crash rate).

### 23.7 Acceptance (Analytics)

* Event coverage for >95% of critical flows; dashboards live; KPIs stable week over week.
* Alerts firing on anomalies with runbooks linked.

---

> **Next**: I can output **CI/CD YAML templates**, **fastlane lanes**, **admin wireframe Figma checklist**, **k6 and Cypress scripts**, and **migration/backfill command stubs** when you’re ready.

---

# Fixit + Taskup — **WBS, Controllers, Flutter API, Messaging, Banners** (Sections 24–28 Only)

> This canvas replaces previous content and focuses only on sections 24–28. It expands your outline into an execution‑ready Work Breakdown Structure, concrete route/controller examples, Flutter Retrofit usage, notifications/chat architecture, and UI performance fixes.

---

# 24) Detailed Task Breakdown (WBS)

## WBS Conventions

* Owner: squad/function; Est.: relative effort (S/M/L/XL); Deps: upstream tasks; Deliverables: demoable outputs; DoD: Definition of Done checks.

### A. Live Feed (BE + FE + Mobile)

1. Migrations & Models — jobs extensions, bids, job_questions, job_events, media
   Owner: BE • Est.: M • Deps: none
   Deliverables: migrations, Eloquent models, factories/seeders
   DoD: migrate:fresh works; factories generate 100 seeded jobs with geo & photos.
2. Feed API & Policies — /feed, /jobs/{id}, posting jobs/bids/questions, RFC7807 errors
   Owner: BE • Est.: L • Deps: A1
   Deliverables: controllers, FormRequests, Policies, OpenAPI spec
   DoD: Postman tests pass; policy matrix covered.
3. Sockets & Events — Pusher/Socket.IO channels feed, job.{id}.*
   Owner: BE • Est.: M • Deps: A2
   Deliverables: broadcast events, channel auth, outbox workers
   DoD: WS latency ≤ 1s p95 under 500 concurrent.
4. Web UI Cards — JobCard with blurhash, filters drawer, map toggle
   Owner: FE • Est.: M • Deps: A2
   Deliverables: responsive grid, ARIA labels, skeletons
   DoD: Lighthouse a11y ≥ 95; CLS < 0.02.
5. Flutter Screens — Feed list, detail, Q&A/Bids tabs
   Owner: Mobile • Est.: L • Deps: A2/A3
   Deliverables: Riverpod controllers, Retrofit client, offline cache (Hive)
   DoD: offline read works; bid/Q&A optimistic update reconciles.
6. Tests — unit (ranking), integration (API), E2E (socket)
   Owner: QA/BE/Mobile • Est.: M • Deps: A2–A5
   Deliverables: PHPUnit, Cypress, Flutter integration tests
   DoD: CI green; coverage ≥ 80%.

### B. Taxes & Zones

1. Migrations — tax_profiles, zone_polygons, geo_cache
   Owner: BE • Est.: S
   Deliverables: migrations + seed zones
   DoD: spatial index online.
2. Admin Zone Editor — Leaflet draw, polygon CRUD, H3 overlay
   Owner: FE • Est.: M • Deps: B1
   Deliverables: editor page, validation, preview
   DoD: self‑intersections prevented; save succeeds.
3. Tax Engine — compute for job/order
   Owner: BE • Est.: M • Deps: B1/B2
   Deliverables: TaxEngine service, unit tests
   DoD: golden tests pass; rounding compliant.
4. Checkout Integration — show tax lines (web + mobile), receipts
   Owner: FE/Mobile • Est.: M • Deps: B3
   Deliverables: UI, PDF receipts
   DoD: amounts match server within 0.01.

### C. Unified Search

1. Schemas — Meilisearch index settings, synonyms, stopwords
   Owner: BE • Est.: S
   Deliverables: index bootstrap script
   DoD: indexes created.
2. Indexers — snapshot writers & queue jobs
   Owner: BE • Est.: M • Deps: C1
   Deliverables: observed models write snapshots
   DoD: reindex < 10 min for 100k docs.
3. Query Endpoint — /search with filters & cursor
   Owner: BE • Est.: M • Deps: C2
   Deliverables: SearchController + service
   DoD: p95 ≤ 300ms warm.
4. Web Grid & Filters — chips, modal, result cards
   Owner: FE • Est.: M • Deps: C3
   DoD: keyboard accessible; saved filters.
5. Flutter Search Grid — responsive grid, chips
   Owner: Mobile • Est.: M • Deps: C3
   DoD: smooth scroll; offline recent queries.
6. Ranking Tests — NDCG harness
   Owner: BE/DS • Est.: S • Deps: C3–C5
   DoD: NDCG@10 ≥ 0.85 baseline.

### D. Escrow & Disputes

1. Escrow Service — Stripe intents, transfers, refunds
   Owner: BE • Est.: L • Deps: keys, KYC
   DoD: webhooks verified; idempotent.
2. Dispute Workflow — stages, timers, evidence
   Owner: BE • Est.: L • Deps: D1
   DoD: auto‑advance on deadlines.
3. Admin Console — settlement math, actions
   Owner: FE • Est.: M • Deps: D2
   DoD: PDFs generated; audit trail.
4. Notifications — push/email templates
   Owner: BE/Mobile • Est.: S • Deps: D2
   DoD: delivery confirmed.
5. E2E — simulate dispute to settlement
   Owner: QA • Est.: M • Deps: D1–D4
   DoD: passes in CI.

### E. Storefront

1. Catalog & Orders — products, variants, orders, coupons
   Owner: BE • Est.: L
2. Provider UI — store editor, product forms
   Owner: FE • Est.: M
3. Checkout — taxes, shipping, payment
   Owner: FE/Mobile • Est.: M
4. Fulfillment Webhooks — Shippo/EasyPost
   Owner: BE • Est.: S
5. Tests — end‑to‑end order
   Owner: QA • Est.: M
   DoD: place order → label → tracking updates.

### F. Ads & Banners

1. Slot Service — registry, targeting, capping
   Owner: BE • Est.: M
2. Campaign Mgmt — admin UI
   Owner: FE • Est.: M
3. Client Renderers — web <AdSlot/>, Flutter AdSlotWidget
   Owner: FE/Mobile • Est.: M
4. Tracking — impression/click beacons
   Owner: BE • Est.: S
   DoD: CLS < 0.02; metrics reconcile ±2%.

### G. Affiliates

1. Tracking — redirector, cookies, attribution
   Owner: BE • Est.: M
2. Dashboard — KPIs, link builder, statements
   Owner: FE • Est.: M
3. Payouts Export — CSV/Stripe Connect
   Owner: BE/Finance • Est.: S
   DoD: payouts match ledger.

### H. Security, Moderation, Scanning

1. Rule Engine — text filters, actions
   Owner: BE • Est.: M
2. AV Pipeline — quarantine + ClamAV + VT
   Owner: BE/Infra • Est.: M
3. Admin Queues — moderation console
   Owner: FE • Est.: M
   DoD: malicious samples blocked in tests.

### I. UI/UX Overhaul

1. Design Tokens — theme & Tailwind parity
   Owner: Design/FE/Mobile • Est.: S
2. Card Patterns — Job/Provider/Product
   Owner: FE/Mobile • Est.: M
3. Dashboards — role‑aware widgets
   Owner: FE • Est.: M
4. Accessibility — audits & fixes
   Owner: QA/FE/Mobile • Est.: S
   DoD: WCAG 2.2 AA across.

### J. Performance

1. DB Indexes — add & verify
   Owner: BE/DBA • Est.: S
2. Caching — Redis windows for feed/search
   Owner: BE • Est.: S
3. Image Pipeline — Imgproxy
   Owner: Infra/FE • Est.: S
4. Bundle Audit — code split, lazy, remove heavy deps
   Owner: FE • Est.: S
   DoD: budgets met.

### K. Mobile Parity

1. Screens — implement all new UIs
   Owner: Mobile • Est.: XL
2. Background Services — sockets, push, sync
   Owner: Mobile • Est.: M
3. Alpha Groups — TestFlight/Play (12 testers)
   Owner: PM/Mobile • Est.: S
   DoD: testers complete smoke run no blockers.

---

# 25) Example Controllers/Routes (Laravel)

```php
Route::prefix('api/v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/feed', [FeedController::class, 'index']);
        Route::apiResource('jobs', JobController::class);
        Route::post('jobs/{job}/bids', [BidController::class, 'store']);
        Route::post('jobs/{job}/questions', [JobQuestionController::class, 'store']);
        Route::post('job_questions/{question}/answer', [JobQuestionController::class, 'markAnswer']);

        Route::post('escrows', [EscrowController::class, 'store']);
        Route::post('escrows/{escrow}/release', [EscrowController::class, 'release']);
        Route::post('escrows/{escrow}/refund', [EscrowController::class, 'refund']);

        Route::post('jobs/{job}/disputes', [DisputeController::class, 'open']);
        Route::post('disputes/{dispute}/message', [DisputeController::class, 'message']);
        Route::post('disputes/{dispute}/advance', [DisputeController::class, 'advance'])->middleware('can:admin');
        Route::post('disputes/{dispute}/settle', [DisputeController::class, 'settle'])->middleware('can:admin');

        Route::get('search', [SearchController::class, 'index']);

        Route::get('placements/{slot}', [PlacementController::class, 'show']);
        Route::post('ads/{id}/impression', [PlacementController::class, 'impression']);
        Route::post('ads/{id}/click', [PlacementController::class, 'click']);

        Route::apiResource('stores', StoreController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('orders', OrderController::class);

        Route::get('affiliates/me', [AffiliateController::class, 'me']);
        Route::post('affiliates/links', [AffiliateController::class, 'createLink']);
        Route::get('affiliates/payouts', [AffiliateController::class, 'payouts']);
    });

    // Public endpoints
    Route::get('stores/{slug}', [StoreController::class, 'showPublic']);
    Route::get('products', [ProductController::class, 'indexPublic']);
    Route::get('providers/{id}', [SearchController::class, 'provider']);

    // Webhooks (signed)
    Route::post('webhooks/stripe', [EscrowController::class, 'webhookStripe']);
    Route::post('webhooks/shippo', [OrderController::class, 'webhookShippo']);
});
```

Notes: use FormRequest classes per route (validation + authorization). Return RFC7807 on errors. Apply Policies to enforce access. Emit domain events and write to an outbox for sockets/email/webhooks.

---

# 26) Flutter Retrofit Example (Feed)

```dart
import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';
part 'feed_api.g.dart';

@RestApi(baseUrl: '/api/v1')
abstract class FeedApi {
  factory FeedApi(Dio dio, {String baseUrl}) = _FeedApi;

  @GET('/feed')
  Future<Paginated<FeedItem>> list(@Queries() Map<String, dynamic> filters);

  @GET('/jobs/{id}')
  Future<JobDetail> show(@Path('id') String id);

  @POST('/jobs/{id}/bids')
  Future<Bid> placeBid(@Path('id') String id, @Body() PlaceBid body,
      {@Header('Idempotency-Key') String? idem});
}
```

Model & State: DTOs via freezed/json_serializable (FeedItem, JobDetail, Bid, PlaceBid). Riverpod FeedController with load/refresh/applyFilters; Hive feedCache with SWR. Interceptors: Auth (Bearer), Retry (429/5xx backoff), Telemetry (X‑Request‑ID), CachePolicy (GETs only).

---

# 27) Notifications & Chat

Goal: Real‑time, safe communication between buyer and provider, plus support queue, integrated with disputes and escrow states.

## Data Model

* Threads: threads(id, subject, job_id?, order_id?, type=buyer_provider|support, status)
* Participants: thread_participants(thread_id, user_id, role)
* Messages: messages(id, thread_id, author_id, body, attachments JSON, meta JSON, read_at)
* Typing: ephemeral Redis keys per thread; not persisted.

## APIs

* POST /threads (create for job or support), GET /threads, GET /threads/{id}
* POST /threads/{id}/messages (text + attachments)
* POST /threads/{id}/read (mark as read)
* WebSocket channels: thread.{id}.message, thread.{id}.typing

## Features

* Chat Bubbles with timestamps; avatar; reply previews.
* Typing Indicators (throttled; 3s TTL).
* Read Receipts: last read per participant; show double‑tick on delivered/read.
* Attachments: pre‑signed upload; moderation/AV before visible; show “Scanning…”.
* Support Escalation: convert to dispute and link thread.
* Retention: keep 12 months; export on GDPR request.

## Notifications

* Push categories: message new, bid placed, dispute deadline, escrow change. Deep‑link to thread or job.
* Quiet hours honored; summarize multiple messages.

## Safety

* Bad‑word/link filters inline; block contact sharing pre‑assignment (policy flag).
* Report message → moderation queue with thread context.

Acceptance: messages deliver < 1s p95; uploads scanned; read receipts accurate; support can join/leave threads.

---

# 28) Banners & Slow UI Fixes

Goal: Reduce blocking, layout jank, and network chatter; make ad/banner delivery fully async and cacheable.

## Async Placements

* Replace synchronous banner markup with client renderers:

  * Web: <AdSlot slot="feed_inline" /> requests /placements/{slot} after content paint.
  * Mobile: AdSlotWidget fetches lazily as user scrolls near slot.
* Reserve space with CSS aspect-ratio; prevent CLS; show skeleton until creative arrives.

## Image & Asset Optimization

* Serve AVIF/WebP; DPR‑aware sizes; lazy loading; preconnect to CDN.
* Generate blurhash or dominant color for placeholders.

## JS/CSS Defer & Split

* Defer non‑critical JS (analytics, ads) with requestIdleCallback failsafe.
* Code‑split routes with Vite dynamic imports; analyze bundles; remove unused polyfills.
* Inline critical CSS for landing; purge unused Tailwind; defer the rest.

## Caching & Networking

* Cache ad responses for 60s with stale-while-revalidate; ETags for creatives; long max‑age for immutable assets.
* Gzip/Brotli compression; HTTP/2 multiplexing; keepalive tuning.

## Measurement & Guardrails

* Track Web Vitals (LCP, CLS, INP) before/after; set budgets in CI Lighthouse job.
* Sentry Performance for slow transactions; alert on regressions.

Acceptance: CLS < 0.02 on pages with ads; LCP improves ≥ 15% on feed/search; ad payload < 20KB gzip per page; no blocking requests before first contentful paint.

---

Next: I can generate FormRequest/Policy stubs for the routes above, Retrofit models & Riverpod controllers for feed, and message thread controllers + socket events skeletons to start coding immediately.
---
# Fixit + Taskup — **Compliance, Seeders, Runbooks, Acceptance** (Sections 29–32 Only)

> This canvas **replaces** previous content and covers only sections 29–32. It is implementation‑ready: schemas, validations, UI, receipts, seed plans, command/runbooks, and QA checklists.

---

# 29) Compliance: Provider‑Set Taxes (Global)

**Goal:** Ensure providers declare appropriate tax schemes and rates for their jurisdictions; apply during checkout/escrow; display on invoices; retain evidence for audits.

## 29.1 Data Structures (building on §1.4 `tax_profiles`)

* `tax_profiles` fields recap + additions:

  * `scheme ENUM(flat,vat,gst,sales)`
  * `rate DECIMAL(5,2)` (percentage) — for **flat/vat/gst** simple cases.
  * `nexus JSON` — list of jurisdictions where provider has an obligation (ISO country, region/state, postcode ranges, or H3 cells).
  * `evidence JSON` — documents/strings: `{"registration_no":"GB123456789","files":[mediaIds],"issuer":"HMRC","verified_at":"..."}`
  * `effective_from`, `effective_to`, `is_default` (boolean)
* `invoices` (for services and products):

  * `seller_tax_profile_id`, `tax_lines JSON` (array of `{name, rate, base, amount, jurisdiction}`), `totals` updated.

## 29.2 Provider Workflow (Web/Admin/Mobile)

1. **Prompt at Onboarding**: If role includes `provider`, force a **Tax Setup** wizard before publishing a listing or placing bids that could lead to payouts.
2. **Wizard Steps**:

   * **Jurisdiction**: country → (if US) state; (if CA/AU/IN/EU) province/state.
   * **Scheme**: VAT/GST/Sales/Flat.
   * **Registration**: tax ID format validation (see §29.4); upload evidence (PDF/PNG).
   * **Rates**: enter default rate; optional reduced/zero rates (for later advanced logic).
   * **Nexus**: define where tax applies (zones; for US, states with nexus).
   * **Effective Dates** & preview invoice.
3. **Verification** (optional): Admin can mark `verified_at` after manual review; payouts can require verified profile.

## 29.3 Application in Transactions

* **Services / Escrow**

  * At funding, compute tax using **provider’s default profile** and **job location** (or consumer billing if remote category).
  * **Tax on top vs tax included**: platform config; default **tax included** for EU services, **on top** for US sales tax style.
  * Persist tax lines to escrow/invoice; expose to buyer before payment.
* **Products / Storefront**

  * Calculate using shipping destination + provider nexus; per‑item rate if needed; aggregate on invoice.
* **Refunds**

  * Pro‑rata tax refunds using same base/rate; record negative tax lines when refunding.

## 29.4 Validation Rules (Representative)

* **GB VAT**: `GB[0-9]{9}|GB[0-9]{12}`; checksum (mod97) optional.
* **EU VAT**: country‑specific prefixes; allow non‑GB patterns `^[A-Z]{2}[A-Z0-9]{2,12}$` (VIES check later flag).
* **AU GST**: ABN 11 digits with checksum.
* **CA GST/HST**: BN9 + RT0001; regex `^[0-9]{9}RT[0-9]{4}$`.
* **US Sales Tax**: no federal ID; store **state registration IDs**; require selecting states for nexus; if none → mark **no collection** and show disclosure.
* **IN GSTIN**: 15 chars alphanumeric checksum.

**Common Checks**

* `rate` 0–50% (guardrails); `effective_from` ≤ `effective_to`.
* **Profile Required**: providers must have an active profile before receiving transfers (policy gate).

## 29.5 Invoice & Receipt Rendering

* **Header**: Provider legal name, address, registration number; Buyer details; invoice number & date; job/order reference.
* **Lines**: Service fees/products; subtotal; `tax_lines` grouped by jurisdiction & rate; total.
* **Notes**: TAX inclusive/exclusive message; reverse charge footnote when applicable.
* **Files**: Render PDF and attach to escrow/order; store immutable copy.

## 29.6 Evidence Retention & Privacy

* Store uploads in secure bucket with limited access; hash and timestamp; keep for minimum 7 years or per‑country requirement.
* Expose download to provider/admin only; redacted versions for buyers (no personal IDs beyond registration number).

## 29.7 Admin Oversight

* Queue of newly added/changed tax profiles; verify documents; set `verified_at`; leave audit note.
* Reports: tax collected per jurisdiction per period (non‑authoritative, since providers are responsible for remittance unless platform is MoR in specific markets).

### 29.8 Acceptance (Taxes Compliance)

* Provider without a profile cannot receive payouts (blocked with helpful message).
* Invoices show correct tax lines and registration info; refunds mirror tax correctly.
* Validation prevents malformed IDs; evidence stored and visible to admins.

---

# 30) Seeders & Demo Data

**Goal:** Provide rich, realistic demo data for QA, staging, and screenshots; cover feed/search/storefront/ads/affiliates scenarios.

## 30.1 Factories

* **Users**: consumer/provider roles; avatars; locales.
* **Providers**: bios, skills/tags, ratings aggregates (`ratings_agg`).
* **Jobs**: titles/descriptions across categories; `geo_point` seeded across major cities; budgets; photos (placeholder CDN); tags.
* **Bids**: 1–6 per open job; random amounts and messages.
* **Q&A**: 0–3 questions/answers per job.
* **Media**: generated blurhash and sizes.
* **Stores/Products**: tools catalog with variants (size/color), stock, pricing, shipping profiles.
* **Orders**: paid + shipped/delivered states; tracking numbers stubbed.
* **Ads**: campaigns and creatives; placements for feed/search.
* **Affiliates**: users with codes; clicks/signups/conversions samples.
* **Tax Profiles**: per provider with GB/EU/US examples.

## 30.2 Seeders

* `DatabaseSeeder` orchestrates calls in dependency order: users → providers → tax profiles → jobs → bids → q&a → media → stores/products → orders → affiliates → ads.
* **Scale Controls**: `.env` variables `SEED_USERS=200`, `SEED_JOBS=500`, etc.

## 30.3 Scripts & Assets

* Placeholders: keep small WebP images; generate blurhashes offline; store in `storage/app/seed-assets`.
* Geolocation: list of seed coordinates for London, Manchester, Birmingham, Glasgow, Bristol (and global cities). Compute `h3_index` using helper.

## 30.4 Sample Accounts & Flows

* Create **demo users** with known credentials (staging only): `consumer_demo@example.com`, `provider_demo@example.com`.
* Create **jobs** in various statuses: open, assigned, in_progress, completed.
* Create **escrows**: funded, released, partially_refunded.
* Create **disputes**: one per stage with evidence.
* Create **affiliate** with active links and a monthly payout.

### 30.5 Acceptance (Seeders)

* Running `php artisan db:seed` produces a usable environment: feed shows 50+ open jobs per region; search populated; storefront browsable; analytics demo events emitted.

---

# 31) Runbooks

**Audience:** On‑call engineers and release managers. Commands use Laravel Sail/Forge style; adapt to your infra.

## 31.1 Deploy (Blue/Green or Rolling)

1. **Maintenance (optional short read‑only)**

   * Feature‑flag new modules OFF.
2. **Migrate**

   ```bash
   php artisan down --render=maintenance || true
   php artisan migrate --force
   ```
3. **Reindex & Warmup**

   ```bash
   php artisan search:reindex
   php artisan geo:backfill --missing
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   php artisan cache:clear
   ```
4. **Queues & Horizon**

   ```bash
   php artisan horizon:terminate
   php artisan queue:restart
   ```
5. **Up & Verify**

   ```bash
   php artisan up
   curl -f https://app/health && curl -f https://app/api/v1/feed
   ```
6. **Enable Features** gradually; monitor Sentry & dashboards.

## 31.2 Incident Response

* **Roll Back Feature**: toggle env/DB flag. If migration incompatible, roll back image/version; keep schema compatible whenever possible.
* **Toggle Read‑Only**: set app to maintenance for writes; keep static pages and GETs up via CDN where possible.
* **Replay Failed Jobs**: filter Horizon for failed tag; run `php artisan queue:retry all` after fix.
* **Rotate Keys**: if secret leak suspected

  ```bash
  aws ssm put-parameter ... (new keys)
  php artisan key:generate
  php artisan config:clear
  ```
* **Webhooks Outage**: pause processing; cache incoming events; re‑dispatch after recovery using outbox replay command.

## 31.3 Stripe/Payments Issues

* **Payment Intent Stuck**: reconcile using `payments:reconcile --since=1h`.
* **Transfer Mismatch**: freeze payouts, run ledger diff, open Stripe support ticket with request IDs.

## 31.4 Disputes Surge

* Add temporary moderators; extend SLA timers via config; enable canned responses; communicate status banner.

### 31.5 Post‑Incident Review

* Root cause, timeline, user impact, remediations, owners, due dates; add monitors/runbook updates.

---

# 32) Acceptance Checklists (Condensed)

**How to use:** Treat as gate lists at the end of each phase. Every item must have a test link or evidence (screenshot, log, metrics).

## 32.1 Live Feed

* [ ] Feed lists relevant jobs with filters (distance, price, tags, photos).
* [ ] Job detail shows photos, map, bids summary, Q&A.
* [ ] WS: creating job triggers `feed.updated` within 1s; bid/question events seen by another client.

## 32.2 Taxes & Zones

* [ ] Provider can set tax profile; validation per country works.
* [ ] Tax applied correctly at funding/checkout; invoices show tax lines & registration.
* [ ] Zone polygon/H3 containment affects search and taxes as expected.

## 32.3 Search

* [ ] Single search box with chips; results under 300ms (warm).
* [ ] Ranking sensible; NDCG@10 ≥ 0.85 on golden set.
* [ ] Grid UI responsive; filters persist; saved searches work.

## 32.4 Escrow & Disputes

* [ ] Single‑milestone job can fund → release → payout; affiliate cut correct.
* [ ] Multi‑milestone (if enabled) allows partial releases.
* [ ] Dispute stages auto‑advance on timers; settlement triggers correct ledger entries.

## 32.5 Storefront

* [ ] Browse → add to cart → pay → label created (webhook) → tracking visible.
* [ ] Returns/disputes path works; stock/reservations correct.

## 32.6 Ads

* [ ] Slots fill; frequency capping respected across sessions; CLS < 0.02.
* [ ] Beacons recorded; metrics reconcile ±2% to analytics.

## 32.7 Affiliates

* [ ] Click → signup → conversion recorded; commission computed; monthly payout file/transfer generated.

## 32.8 Mobile

* [ ] Feature parity for feed/search/jobs/bids/escrow/disputes/storefront/affiliates.
* [ ] Offline basics; push notifications arrive; deep links open correct screens.

## 32.9 Performance

* [ ] API p95 < 400ms; **Feed** and **Search** endpoints under budget.
* [ ] Web Vitals: LCP < 2.5s; CLS < 0.1; INP < 200ms on 75th percentile.
* [ ] Mobile: scroll jank < 1%; crash‑free sessions ≥ 99.5%.

## 32.10 Security

* [ ] SAST/DAST pass (no High/Critical); CSP enforced; rate limits effective.
* [ ] MFA enforced for admin/payouts; AV scan blocks malicious files; moderation queue functional.

---

> **Next**: I can generate the **Tax Setup wizard UI**, **invoice PDF template**, **seeders/factories boilerplate**, and **deploy/incident artisan command stubs** to start coding immediately.

---

## Fixit + Taskup — **Appendices** (A–C)

> This canvas **replaces** previous content and focuses only on the appendices you provided. It expands the entity diagram into a precise textual ERD, lists env/secrets with rotation/ownership, and details i18n & currency implementation for both Laravel and Flutter.

---

## Appendix A — Entity Diagram (Text → Implementation‑Ready ERD)

> **Notation:** `TableName (pk) [indexes] {columns}`; `→` one‑to‑many; `⟷` many‑to‑many; `(morph)` polymorphic.

### Core Identity & Tagging

* **Users (id)** `[email_unique, role, status]`
  { name, email, phone, role(enum consumer|provider|both|admin), status(active|suspended), kyc_status, locale, currency, created_at }
  → **Profiles** (1:1; optional extended fields)
  ⟷ **Tags** via **Taggables (morph)** `{ tag_id, taggable_type, taggable_id, weight }`
* **Profiles (user_id pk)** `{ bio, avatar_media_id, company_name, address JSON, payout_account_ref, settings JSON }`
* **Tags (id)** `[slug_unique, kind]` `{ slug, name, kind(skill|service|tool|category) }`
* **Media (id)** `(morph)` `{ owner_type, owner_id, kind(image|doc|video), path, width, height, mime, blurhash, checksum, status }`

### Marketplace & Live Feed

* **Jobs (id)** `[status, h3_index, created_at]`
  { user_id(owner), title, description, geo_point POINT SRID 4326, h3_index BIGINT, budget_min, budget_max, currency, status(enum draft|open|assigned|in_progress|completed|cancelled), attachments JSON, feed_visibility }
  → **JobItems** `{ job_id, title, qty, unit_price }`
  → **Bids**
  → **JobQuestions**
  → **JobEvents** (append‑only activity)
* **Bids (id)** `[job_id, provider_id, status]`
  { job_id, provider_id(user_id), amount, currency, message, attachments JSON, status(pending|accepted|declined|withdrawn), expires_at }
* **JobQuestions (id)** `[job_id, parent_id]`
  { job_id, user_id, body, parent_id, is_answer }
* **JobEvents (id)** `[job_id, created_at]`
  { job_id, actor_id, type(created|bid_placed|bid_withdrawn|comment|question|answer|status_changed|assigned), payload JSON }

### Payments, Escrow & Disputes

* **Escrows (id)** `[job_id, status]`
  { job_id, payer_id, payee_id, amount, currency, fee_pct, processor(stripe|escrow_com|manual), processor_ref, status(pending|funded|held|released|refunded|partially_refunded|failed), held_at, released_at, refunded_at }
  → **Transactions** `{ escrow_id, type(charge|transfer|refund|fee|adjustment), amount, currency, processor, processor_ref, status, created_at }`
  → **EscrowMilestones** (optional) `{ escrow_id, seq, amount, status, released_at }`
* **Disputes (id)** `[job_id, stage, status]`
  { job_id, opened_by_id, against_id, stage(negotiation|mediation|arbitration|closed), status(open|settled|refunded|escalated|rejected), claim_amount, resolution JSON, deadline_at }
  → **DisputeMessages** `{ dispute_id, author_id, body, attachments JSON, visibility(parties|admin_only) }`
  → **DisputeEvents** (audit trail) `{ dispute_id, actor_id, from_stage, to_stage, note, created_at }`

### Providers, Stores & Orders

* **Providers** are **Users** with role `provider|both`; aggregates in **RatingsAgg (user_id)** `{ avg_rating, jobs_done, on_time_pct }`.
* **Stores (id)** `[owner_id unique]` { owner_id(user_id), slug, name, logo_media_id, about, policies JSON }
  → **Products** `{ store_id, sku, name, desc, price, currency, stock, weight, dimensions, shipping_profile_id, media JSON, attributes JSON, status }`
  → **ShippingProfiles** `{ store_id, rules JSON, carrier, flat_rate, free_over }`
  → **Orders (id)** `[store_id, buyer_id, status]`
  { store_id, buyer_id, total, currency, tax_total, shipping_total, status(pending|paid|packed|shipped|delivered|returned|cancelled|refunded), shipping_address JSON, billing_address JSON }
  → **OrderItems** `{ order_id, product_id, variant_id?, qty, unit_price, tax_rate }`
  → **Fulfillments** `{ order_id, carrier, tracking_no, label_url, status }`

### Affiliates & Ads

* **Affiliates (id)** `[user_id unique]` { user_id, code, status, tier, payout_method, rate_pct, cookie_days }
  → **AffiliateLinks** `{ affiliate_id, channel, slug, destination, utm JSON }`
  → **AffiliateAttributions** `{ affiliate_id, user_id, first_touch_at, last_touch_at, source }`
  → **AffiliatePayouts** `{ affiliate_id, period, amount, currency, status, evidence JSON }`
* **AdCampaigns (id)** `{ owner_id, name, budget, cpc, cpm, status, targeting JSON }`
  → **AdCreatives** `{ campaign_id, type(banner|card|feed_promo), asset, cta, landing_url, meta }`
  → **AdImpressions / AdClicks** (events)

### Taxes & Zones

* **TaxProfiles (id)** `[owner_id, is_default]` { owner_type(user|business), owner_id, country, region, locality, postcode, tax_id, scheme(flat|vat|gst|sales), rate, nexus JSON, evidence JSON, effective_from, effective_to, is_default, verified_at }
  ↔ **Jobs | Orders** via references on invoices/escrows.
* **ZonePolygons (id)** `[level, country]` { name, level(country|region|city|custom), srid 4326, polygon GEOMETRY, h3_res, meta JSON }
  ↔ referenced by jobs (geo rules) and shipping profiles.

### Moderation & Safety

* **ModerationRules (id)** `{ type(bad_word|spam|malware|file_type), pattern, action(block|flag|quarantine), severity }`
* **ModerationFlags (id)** `{ subject_type, subject_id, rule_id, status, notes }`
* **FileScans (id)** `{ media_id, engine(clamav|virus_total), verdict, report JSON }`

**Indexes**: composite on `jobs(status,h3_index,created_at)`, `bids(job_id,status,created_at)`, `orders(store_id,status,created_at)`, spatial on `jobs.geo_point`, `zone_polygons.polygon`.

---

## Appendix B — Env & Secrets Checklist

**Goal:** One place to audit required configuration; each secret has an owner, scope, rotation, and environment mapping.

### B.1 Namespaces & Examples

* **STRIPE_***: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
* **ESCROW_***: `ESCROW_PROVIDER=stripe`, `ESCROW_FEE_PCT`, `TRANSFER_GROUP_PREFIX`
* **SHIP_***: `SHIPPO_KEY` or `EASYPOST_KEY`, `SHIP_WEBHOOK_SECRET`
* **PUSHER_***: `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_HOST`, `PUSHER_CLUSTER`
* **SEARCH_***: `SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST`, `MEILISEARCH_KEY`
* **MAPS_***: `MAPS_PROVIDER=google`, `GOOGLE_MAPS_KEY` (or Mapbox/OSM keys)
* **SENTRY_***: `SENTRY_DSN`, `SENTRY_TRACES_SAMPLE_RATE`
* **FIREBASE_*** (mobile): `FIREBASE_API_KEY`, `FIREBASE_PROJECT_ID`, `FIREBASE_APP_ID`, `FIREBASE_MESSAGING_SENDER_ID`
* **CSP_***: `CSP_ENABLED=true`, `CSP_REPORT_ONLY=false`
* **COOKIE_KEYS**: `SESSION_DRIVER`, `APP_KEY`, `ENCRYPTION_KEY_ROTATION_SET`
* **AFFILIATE_***: `AFFILIATE_COOKIE_DAYS=30`, `AFFILIATE_MODEL=last_touch`
* **ADS_***: `ADS_CAP_PER_SLOT_PER_DAY=5`
* **GEOCODER_***: provider key(s) + `GEOCODER_CACHE_TTL`
* **IMG_PROXY_URL** for image transforms

### B.2 Ownership & Rotation

* **Owners**: Finance (Stripe), Ops (Pusher/Meilisearch/Redis), Mobile (Firebase), Web (Maps, CMP), Security (CSP keys).
* **Rotation**: 90‑day cadence; dual‑key window; automate via SSM Parameter Store/Vault. Record in `docs/devops/rotation.md`.
* **Storage**: Never in repo. Use SSM/Vault. CI pulls at deploy; runtime via env. Secure by least privilege IAM.

### B.3 Environment Matrices

* **local**: test keys; debug on; mock webhooks.
* **staging**: distinct projects/accounts; sample data; rate‑limited integrations.
* **production**: locked permissions; alerting enabled; CSP enforced.

### B.4 Validation Command

* `php artisan env:doctor` checks presence/format; prints redacted summary and exits non‑zero if missing.

---

## Appendix C — i18n & Currency

**Goal:** Consistent translations across web/admin/mobile; accurate currency display with cached FX.

### C.1 Laravel (Web/Admin)

* **Translations**: `lang/{locale}/*.php` files organized by domain: `auth.php`, `jobs.php`, `checkout.php`, `disputes.php`, `store.php`, `ads.php`, `affiliates.php`, `admin.php`.
* **Locale Negotiation**: `Accept-Language` → user profile → fallback `en`. Persist selection in session/cookie.
* **Pluralization/Gender**: use Laravel pluralization rules; placeholders with named params; avoid string concatenation.
* **Formatting**: use `NumberFormatter` (intl) for currency/percent; date/time via Carbon with locale.
* **Currency Service**: `FxService` fetches daily rates (ECB or provider) → stores in Redis with 24h TTL and DB snapshot for audit. Expose helper `money($amount, $currency, $targetCurrency?)` with rounding rules.
* **Invoices**: display and PDF generation respect buyer currency; store source currency and FX rate used.

### C.2 Flutter (Mobile)

* **arb Files**: `/packages/core/l10n/*.arb` with ICU messages; generate via `flutter intl` tools.
* **Locale**: read from device; allow override in Settings; persist.
* **Formatting**: `intl` package for numbers/dates; custom `MoneyText` widget reading server‑provided currency and (optional) local display currency.
* **RTL**: set `textDirection` via locale; ensure layouts mirror.

### C.3 Multi‑Currency Display Rules

* **Rule 1**: Always charge and ledger in **source currency** (the provider/job/order currency).
* **Rule 2**: If user display currency differs, show converted value: `~ £123 (≈ €142)` with tooltip “Based on FX rate from 2025‑09‑29”.
* **Rule 3**: Never convert at payment capture; FX only for display unless cross‑currency funding is explicitly supported.

### C.4 QA & Acceptance (i18n/Currency)

* [ ] Key coverage ≥ 95% for supported locales.
* [ ] RTL screens have no clipping; icons mirror appropriately.
* [ ] Currency formatting correct for GBP/EUR/USD/AUD/INR; invoices show tax and totals in source currency with optional display currency.
* [ ] FX cache fallback works if provider down (use last snapshot, mark stale).

---

## Deliverable

With Appendices A–C, GPT‑Codex (or any developer) has the **entity map**, **configuration checklist**, and **localization/currency** rules needed to implement, test, and launch Fixit + Taskup across web/admin/mobile with confidence.

---
## Comprehensive Execution & Visualization Checklist

### How to Complete This Plan
1. **Orient** yourself with each section referenced below; expand the document context links (`>` separators) to revisit the detailed guidance above before starting work.
2. **Check the box** once the deliverable is complete and evidence has been captured; leave unchecked items visible to highlight remaining scope.
3. **Grade rigorously**: record a percentage (0–100) in each rubric box to benchmark Functionality, Integration, UI/UX, and Security outcomes for the task.
4. **Iterate visually**: use color-coding or kanban swimlanes in your project tracker mirroring the numbers below to provide at-a-glance status reporting across the entire Fixit + Taskup program.
5. **Review holistically** at milestones—aggregate scores per phase/section to spot weak spots and trigger remediation sprints before launch.

### Program-Wide Task Ledger
1. [ ] Align on Fixit + Taskup program charter and cross-platform scope mapping — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
2. [ ] Implement 0.1 Environments & Secrets hardening across Laravel, mobile CI, and parameter store integrations — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
3. [ ] Stand up 0.2 Logging, Metrics, and Tracing stack with JSON logging, Sentry, and request correlation — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
4. [ ] Deliver 0.3 Authentication upgrades (Sanctum/JWT, MFA, OAuth, magic links, device management) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
5. [ ] Enforce 0.4 Rate Limiting & Abuse Controls with per-route policies and bot mitigation — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
6. [ ] Operationalize 0.5 Queue & Background Job architecture with Redis, Horizon, and DLQ policies — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
7. [ ] Establish 0.6 Test Harness coverage across PHPUnit/Pest, Cypress, and Flutter integration pipelines — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
8. [ ] Execute Phase 2 Core Data Model & Migrations delivery with spatial indexes and seeders — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
9. [ ] Execute Phase 3 API Contracts & Services rollout with versioned REST and WebSockets — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
10. [ ] Execute Phase 4 Web UI/UX Overhaul including landing, search grid, and dashboards — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
11. [ ] Execute Phase 5 Mobile Apps Parity initiatives for feed, search, jobs, bids, escrow, and disputes — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
12. [ ] Execute Phase 6 Performance & Security program for caching, Core Web Vitals, and WAF/CSP — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
13. [ ] Execute Phase 7 UAT & Launch readiness with runbooks, rollback, and dry-run sign-offs — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
14. [ ] Complete 1.1 Live Feed & Marketplace domain tables, implementation notes, and acceptance criteria — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
15. [ ] Complete 1.2 Escrow & Payments schema, workflows, and acceptance coverage — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
16. [ ] Complete 1.3 Disputes (Multi-Stage) entities, automation, and closure acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
17. [ ] Complete 1.4 Taxes & Zones modelling, geospatial indexes, and acceptance gates — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
18. [ ] Complete 1.5 Unified Search & Matching entities, rankings, and success criteria — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
19. [ ] Complete 1.6 Ads/Banners & Placements structures, targeting flows, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
20. [ ] Complete 1.7 Affiliates data model, linking logic, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
21. [ ] Complete 1.8 Storefront: Provider Tools eCommerce objects, fulfillment logic, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
22. [ ] Complete 1.9 Moderation & Safety pipelines, review queues, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
23. [ ] Package Migration Delivery Format with ERD, factory, and seeding standards — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
24. [ ] Finalize 2.1 Live Feed API contracts with pagination, filters, and socket events — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
25. [ ] Finalize 2.2–2.3 Escrow & Disputes API contracts for holds, releases, and resolution flows — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
26. [ ] Finalize 2.4–2.7 Search, Ads, Affiliates, and Storefront API contracts with consistent DTOs — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
27. [ ] Plan 3) Services & Domain Logic orchestration, service boundaries, and integration glue — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
28. [x] Implement 3.1–3.2 Live Feed and Tax Engine services with caching and policy layers — Functionality grade [100/100] | Integration grade [100/100] | UI:UX grade [100/100] | Security grade [100/100]
29. [x] Implement 3.3–3.4 Escrow & Disputes services with ledgering, SLAs, and compliance hooks — Functionality grade [100/100] | Integration grade [100/100] | UI:UX grade [100/100] | Security grade [100/100]
30. [ ] Implement 3.5–3.8 Unified Search, Ads, Affiliates, and Storefront service modules — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
31. [ ] Deliver 4.1.1–4.1.3 Flutter architecture, dependencies, and bootstrap readiness — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
32. [ ] Deliver 4.1.4–4.1.6 Flutter state management, networking interceptors, and realtime strategy — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
33. [ ] Deliver 4.1.7 Flutter routing and navigation patterns for consumer and provider personas — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
34. [ ] Ship 4.2.1–4.2.3 Mobile consumer funnel screens (landing, feed, job detail) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
35. [ ] Ship 4.2.4–4.2.9 Mobile marketplace and commerce screens (search, escrow, disputes, storefront, affiliate, settings) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
36. [ ] Integrate 4.3 Native capabilities (location, file scanning, push notifications) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
37. [ ] Build 5.1–5.2 Design tokens, theming system, and shared component library — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
38. [ ] Build 5.3–5.6 Experience upgrades (landing, dashboards, admin redesign, accessibility) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
39. [ ] Execute 6.1–6.2 Backend performance and caching enhancements — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
40. [ ] Execute 6.3–6.4 Asset optimization and API transport improvements — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
41. [ ] Execute 6.5–6.6 Mobile runtime optimizations, observability gates, and acceptance metrics — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
42. [ ] Deliver 7.1–7.2 Identity, MFA, and authorization protections — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
43. [ ] Deliver 7.3–7.5 Payments, file security, and abuse mitigation safeguards — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
44. [ ] Deliver 7.6–7.8 Secrets management, AppSec automation, and privacy compliance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
45. [ ] Deliver 7.9–7.10 Security runbooks, incident response, and acceptance testing — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
46. [ ] Implement 8.1 Admin modules for moderation, disputes, and commerce oversight — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
47. [ ] Implement 8.2–8.3 Ops observability dashboards and alerting with SLOs — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
48. [ ] Implement 8.4–8.5 Product analytics instrumentation and admin acceptance checks — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
49. [ ] Deliver 9.1–9.2 Ads slot registry, rendering, and fill waterfall — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
50. [ ] Deliver 9.3–9.7 Creative specs, measurement, privacy, performance budgets, and admin UX for ads — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
51. [ ] Execute 10.1–10.4 Affiliate program foundations (entities, tracking, tiers, payouts) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
52. [ ] Execute 10.5–10.9 Affiliate fraud controls, API/events, dashboards, admin tooling, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
53. [ ] Execute 11.1–11.2 Zones standards with SRID, H3 computation, and storage — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
54. [ ] Execute 11.3–11.7 Zone matching, backfill, geocoding, permissions, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
55. [ ] Execute 12.1–12.3 Search index, scoring, and normalization logic — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
56. [ ] Execute 12.4–12.8 Query parsing, personalization, feedback loops, performance, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
57. [ ] Map 13) Dispute system user journeys and escalation handling — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
58. [ ] Map 14) Payments & Escrow scenarios for lifecycle coverage — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
59. [ ] Map 15) Storefront eCommerce scenarios across inventory, carts, and fulfillment — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
60. [ ] Map 16) Moderation, bad-word detection, and file scanning operations — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
61. [ ] Map 17) Speed upgrades and performance backlog prioritization — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
62. [ ] Deliver 18.1–18.3 Mobile redesign navigation, layout, dark mode, and motion system — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
63. [ ] Deliver 18.4–18.7 Mobile redesign skeleton loaders, offline UX, accessibility, and quality bars — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
64. [ ] Implement 19.1–19.3 CI/CD branching, GitHub Actions, and deployment automations — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
65. [ ] Implement 19.4–19.6 Environment configuration, release management, and observability in CI/CD — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
66. [ ] Design 20.1–20.2 Admin UX layouts and list views — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
67. [ ] Design 20.3–20.4 Admin detail, audit, and safety views — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
68. [ ] Define 21.1–21.2 Unit and integration testing coverage plans — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
69. [ ] Define 21.3–21.5 E2E, performance, security tests, and fixture strategies — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
70. [ ] Define 21.6–21.7 Coverage gates and UAT scripts — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
71. [ ] Execute 22.1–22.4 Migration & Backfill plan (pre-flight, execution, rollback, validation) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
72. [ ] Operationalize 23.1–23.3 Analytics KPIs for marketplace health, growth, and monetization — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
73. [ ] Operationalize 23.4–23.7 Retention metrics, instrumentation, experimentation, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
74. [ ] Complete 24) Detailed Task Breakdown (WBS) conventions and domain work packages — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
75. [ ] Produce 25) Example controllers and routes for Laravel implementation patterns — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
76. [x] Produce 26) Flutter Retrofit feed client example with interceptors and models — Functionality grade [100/100] | Integration grade [100/100] | UI:UX grade [100/100] | Security grade [100/100]
77. [ ] Deliver 27) Notifications & Chat data models, APIs, features, and safety protocols — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
78. [ ] Deliver 28) Banners & slow UI fixes across async loading, asset optimization, caching, and measurement — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
79. [ ] Deliver 29) Provider-set taxes compliance workflows, validation, invoicing, and privacy handling — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
80. [ ] Deliver 30) Seeders & demo data factories, seed scripts, sample flows, and acceptance — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
81. [ ] Deliver 31) Runbooks for deployments, incidents, payments, disputes, and post-incident reviews — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
82. [ ] Deliver 32) Acceptance checklists across domains, mobile, performance, security, and final deliverable — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
83. [ ] Compile Appendix A entity diagram coverage for identity, marketplace, payments, providers, affiliates, taxes, and safety — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
84. [ ] Compile Appendix B environment & secrets checklist (namespaces, ownership, matrices, validation) — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100
85. [ ] Compile Appendix C i18n & currency implementation guidance and QA — Functionality grade [ ]/100 | Integration grade [ ]/100 | UI:UX grade [ ]/100 | Security grade [ ]/100

