# Fixit + Taskup Full Upgrade Guide (Web + Mobile) — Developer Playbook for GPT‑Codex

**Scope:** End‑to‑end upgrade of **Fixit** (local services marketplace) and companion **Taskup** components across **Laravel (PHP 8.2+) web app + Admin**, and **Flutter (Dart 3) iOS/Android user & provider apps**. Deliver: Airtasker‑style Live Feed, global tax & zones, unified search, escrow + disputes, storefront/eCommerce for tools, affiliates, ads, security, and full UI/UX overhaul. Includes database migrations, APIs, background jobs, analytics, observability, and CI/CD.

> **Stack Assumptions**
>
> * Web: Laravel 10/11 (PHP 8.2+), MySQL 8.x, Redis (cache/queue), Horizon, Scout + Meilisearch/Elasticsearch, Sanctum/JWT, Pusher/Socket.IO, S3‑compatible storage, Maps (Google/OSM), Geospatial (MySQL SRID 4326 + H3).
> * Mobile: Flutter 3.x, Riverpod/Bloc, Dio/Retrofit, Firebase (FCM), Sentry, in‑app purchases (optional later), Google/Apple Pay via Stripe.
> * DevOps: Nginx/Apache, Node 20 for asset build (Vite), Docker for local, GitHub Actions CI/CD, Fastly/Cloudflare CDN, Imgproxy/Thumbor.

---

## 0) High‑Level Program Plan

**Phases & Milestones**

1. **Foundation & Hardening** (Week 1): env & secrets, logging, SSO, rate limiting, background queues, test harness.
2. **Core Data Model & Migrations** (Week 1–2): Live Feed, Bids, Q&A, Escrow, Disputes, Taxes, Zones, Search entities, Ads/Banners, Affiliates, Storefront.
3. **API Contracts & Services** (Week 2–4): REST + WebSocket events; pagination; filters; policy/ACL.
4. **Web UI/UX Overhaul** (Week 3–6): Landing before login, unified search grid, dashboards, admin redesign.
5. **Mobile Apps Parity** (Week 4–7): Live Feed, search, jobs, bids, escrow, disputes, storefront, affiliates.
6. **Performance & Security** (Week 5–7): caching, indexes, bundles, image pipeline, CSP, WAF, SAST/DAST.
7. **UAT & Launch** (Week 7–8): test suites, seeders, migration plan, rollback, runbooks.

**Acceptance Gate** per module: ✅ Unit > ✅ Integration > ✅ E2E > ✅ Security > ✅ Perf > ✅ Accessibility > ✅ i18n.

---

## 1) Data Model (Migrations Cheatsheet)

> Run `php artisan make:migration` per table; all tables InnoDB, utf8mb4. Use UUIDs for pub IDs, bigints internally. Add soft‑deletes where relevant.

### 1.1 Live Feed & Marketplace

* `jobs` (existing): add `feed_visibility`, `geo_point POINT SRID 4326`, `h3_index BIGINT`, `attachments JSON`, `status ENUM(draft,open,assigned,in_progress,completed,cancelled)`, `budget_min`, `budget_max`, `currency`.
* `job_items` (optional breakdown): `job_id`, `title`, `qty`, `unit_price`.
* `bids`: `job_id`, `provider_id`, `amount`, `message`, `attachments JSON`, `status ENUM(pending,accepted,declined,withdrawn)`, `expires_at`.
* `job_questions`: `job_id`, `user_id`, `body`, `parent_id` (threads), `is_answer`.
* `job_events` (feed activity): `job_id`, `actor_id`, `type ENUM(created,bid_placed,bid_withdrawn,comment,question,answer,status_changed,assigned)`, `payload JSON`.
* `media` (polymorphic): `owner_type`, `owner_id`, `kind`, `path`, `width`, `height`, `mime`, `blurhash`.

### 1.2 Escrow & Payments

* `escrows`: `id`, `job_id`, `payer_id`, `payee_id`, `currency`, `amount`, `fee_pct`, `processor ENUM(stripe,escrow_com,manual)`, `processor_ref`, `status ENUM(pending,funded,held,released,refunded,partially_refunded,failed)`, `held_at`, `released_at`, `refunded_at`.
* `transactions`: `escrow_id`, `type ENUM(charge,transfer,refund,fee)`, `amount`, `currency`, `processor`, `processor_ref`, `status`.

### 1.3 Disputes (Multi‑Stage)

* `disputes`: `id`, `job_id`, `opened_by_id`, `against_id`, `stage ENUM(negotiation,mediation,arbitration,closed)`, `status ENUM(open,settled,refunded,escalated,rejected)`, `claim_amount`, `resolution JSON`, `deadline_at`.
* `dispute_messages`: `dispute_id`, `author_id`, `body`, `attachments JSON`, `visibility ENUM(parties,admin_only)`.
* `dispute_events`: audit trail of status/stage transitions.

### 1.4 Taxes & Zones

* `tax_profiles`: `owner_type(user|business)`, `owner_id`, `country`, `region`, `locality`, `postcode`, `tax_id`, `scheme ENUM(flat,progressive,vat,gst,sales)`, `rate DECIMAL(5,2)`, `nexus JSON`, `effective_from`, `effective_to`, `is_default`.
* `zone_polygons`: `name`, `country`, `level ENUM(country,region,city,custom)`, `srid INT DEFAULT 4326`, `polygon GEOMETRY`, `h3_res TINYINT`, `meta JSON`.
* `geo_cache`: forward/reverse geocode cache with TTL.

### 1.5 Unified Search & Matching

* `tags`: normalized; `slug`, `name`, `kind ENUM(skill,service,tool,category)`.
* `taggables`: morph pivot.
* `search_snapshots`: denormalized doc JSON for Meilisearch/ES.
* `ratings_agg`: `user_id`, `avg_rating`, `jobs_done`, `on_time_pct`.

### 1.6 Ads/Banners & Placements

* `ad_campaigns`: `name`, `owner_id`, `budget`, `cpc`, `cpm`, `status`, `targeting JSON`.
* `ad_creatives`: `campaign_id`, `type ENUM(banner,card,feed_promo)`, `asset`, `cta`, `landing_url`, `meta`.
* `ad_impressions` / `ad_clicks` events tables (append‑only).

### 1.7 Affiliates

* `affiliates`: `user_id`, `code`, `status`, `tier`, `payout_method`, `rate_pct`, `cookie_days`.
* `affiliate_links`: `affiliate_id`, `channel`, `slug`, `destination`, `utm JSON`.
* `affiliate_attributions`: `affiliate_id`, `user_id`, `first_touch_at`, `last_touch_at`, `source`.
* `affiliate_payouts`: `affiliate_id`, `period`, `amount`, `currency`, `status`, `evidence JSON`.

### 1.8 Storefront: Provider Tools eCommerce

* `stores`: `owner_id`, `slug`, `name`, `logo`, `about`, `policies JSON`.
* `products`: `store_id`, `sku`, `name`, `desc`, `price`, `currency`, `stock`, `weight`, `dimensions`, `shipping_profile_id`, `media JSON`, `attributes JSON`, `status`.
* `shipping_profiles`: `store_id`, `rules JSON` (zone‑based), `carrier`, `flat_rate`, `free_over`.
* `orders`: `store_id`, `buyer_id`, `total`, `currency`, `status`, `shipping_address JSON`, `billing_address JSON`, `tax_total`, `shipping_total`.
* `order_items`: `order_id`, `product_id`, `qty`, `unit_price`, `tax_rate`.
* `fulfillments`: `order_id`, `carrier`, `tracking_no`, `label_url`, `status`.

### 1.9 Moderation & Safety

* `moderation_rules`: `type ENUM(bad_word,spam,malware,file_type)`, `pattern`, `action`, `severity`.
* `moderation_flags`: `subject_type`, `subject_id`, `rule_id`, `status`, `notes`.
* `file_scans`: `media_id`, `engine ENUM(clamav,virus_total)`, `verdict`, `report JSON`.

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

## 4) Mobile Apps (Flutter) — Parity & UX

### 4.1 Architecture

* Packages: `core`, `auth`, `feed`, `search`, `jobs`, `bids`, `chat`, `payments`, `disputes`, `store`, `ads`, `profile`, `settings`.
* State: Riverpod; Offline cache (Hive) for feed/search results.
* Networking: Dio + Retrofit; Interceptors (auth, retries, gzip, telemetry).
* Realtime: Pusher/socket_io with background isolates for push events.

### 4.2 Screens

* **Pre‑login Landing**: marketing carousel, sign‑in, discover (read‑only feed), SEO deep links.
* **Feed**: Airtasker‑style cards (photos, price range, distance, time). Filters drawer. Map toggle.
* **Job Detail**: photos, description, location map, Q&A, bids tab, CTA: Place Bid.
* **Search (Unified)**: single bar + chips; grid cards for services/providers/products; advanced filter modal.
* **Escrow & Payments**: funding, statuses, receipts.
* **Disputes**: stage tracker, upload evidence, admin chat.
* **Storefront**: product grid, cart/checkout, shipping selection, order tracking.
* **Affiliate**: referral link, stats, earnings.
* **Settings**: tax profile, zones, notifications, KYC, payouts.

### 4.3 Native Integrations

* Location services with throttled updates + geofencing around zones; graceful permission handling.
* File scanning before upload (client‑side heuristic + server AV).
* Push notifications: feed nearby jobs, bid updates, dispute deadlines.

---

## 5) UI/UX Overhaul

* **Design Tokens:** Tailwind config + Flutter theme parity; accessible contrasts; rounded‑2xl, shadows, card grid.
* **Card Patterns:** JobCard, ProviderCard, ProductCard with skeleton loaders and shimmer.
* **Landing Before Login:** protect internal index; route `/landing` → login → `/app`.
* **Dashboards:** role‑aware widgets (earnings, leads, tasks, disputes, affiliate, store orders).
* **Admin Redesign:** resource tables with quick filters, bulk actions, audit trail, impersonate.
* **Accessibility:** WCAG 2.2 AA; keyboard traps; screen reader labels.

---

## 6) Performance Plan

* DB: composite indexes (`jobs(status,h3_index)`, `bids(job_id,status)`, `products(store_id,status)`).
* Cache: Redis for feed queries (windowed by h3 & filters). Tag‑based invalidation on job change.
* Assets: Vite code‑split, lazy routes, prefetch; image transforms via Imgproxy with WebP/AVIF.
* API: compression, HTTP/2, stale‑while‑revalidate; N+1 guards; DTO transformers.
* Mobile: paging, image placeholders, background prefetch, LRU cache.

---

## 7) Security & Compliance

* Auth: Passwordless email magic link + OAuth; MFA; device sessions; refresh token rotation.
* RBAC/Policies: explicit policies for jobs/bids/escrow/disputes/admin.
* Payments: PCI‑aware (Stripe Elements/SDK); no card data server‑side.
* Files: AV scan (ClamAV) + MIME sniff + size/type whitelist; quarantine pipeline.
* Content: bad‑word filters (locale aware), spam throttles, link safety (redirector + safebrowsing).
* Secrets: Vault/Parameter Store; .env templates; key rotation.
* AppSec: SAST (Larastan/PHPStan), DAST (ZAP), dependency audit; CSP; rate limits.
* Privacy: GDPR toggles (export/delete); audit logging; cookie banner & prefs.

---

## 8) Admin & Ops Tooling

* **Admin Modules:** Dispute console (stage transitions, timers), Escrow ledger, Tax profiles, Zone editor (draw/save polygons), Ad manager (slots/campaigns/creatives), Affiliate manager, Store moderation, Content flags queue.
* **Observability:** Sentry, Laravel Telescope, Horizon, Prometheus + Grafana (APM), access logs to BigQuery/S3.
* **Analytics:** Event schema (feed_view, bid_place, escrow_fund, dispute_open, ad_click, affiliate_convert). BigQuery or ClickHouse.

---

## 9) Ads/Banners Upgrades

* Slot registry with rendering components (web + mobile). Client will request `/placements/{slot}`; server returns waterfall list; rotate; capping via Redis keys. Creative validation + image optimization.

---

## 10) Affiliate Program (Extensive)

* Tiers (e.g., 5% default; admin configurable). Cookie window (30–90d). First‑touch/last‑touch models. Payout cycles monthly with export to CSV and Stripe Connect/Bank.
* Dashboard: clicks, signups, conversions, pending/paid; deep links with channel tags.

---

## 11) Zones & Location Logic

* Normalize all lat/lng to SRID 4326; compute `h3_index` at res 8–9. Zone matching by polygon `ST_Contains` or h3 containment. Background job to backfill for legacy rows.
* Geocoding: components‑first (country + postal_code). Cache results with TTL + checksum of input.

---

## 12) Search Algorithm & Ranking

* Scoring factors (weights adjustable): text relevance, distance proximity, provider rating, completion rate, response time, price fit, recency, availability, tag match. Learning‑to‑rank optional later.

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

## 18) Mobile App Redesign Notes

* Modern nav (bottom bar + FAB); map mini‑cards; dark mode; animations with implicit animations only where cheap; skeleton loaders; offline toasts.

---

## 19) CI/CD & Environments

* **Branches:** `main`, `develop`, feature branches. PR checks: PHPStan, Pint, PHPUnit, eslint/tsc, Flutter analyze/test, DAST.
* **Actions:** build web, run tests, build Flutter (fastlane lanes), upload to TestFlight/Play.
* **Env Vars:** `PAYMENTS_*`, `ESCROW_*`, `GEOCODER_*`, `SEARCH_*`, `ADS_*`, `AFFILIATE_*`, `SHIP_*` (template provided below).

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

---

## 20) Admin UX Wireframe (Text)

* Left nav: Dashboard, Feed, Jobs, Bids, Disputes, Escrows, Taxes, Zones, Stores, Products, Orders, Ads, Affiliates, Moderation, Users, Settings, Logs.
* Each list: quick filters, saved views, export CSV, bulk actions, per‑row audit.

---

## 21) Testing Strategy

* **Unit:** services (tax, escrow, ranking). **Integration:** API endpoints, webhooks, file pipeline. **E2E:** Cypress (web), Flutter integration tests.
* **Performance:** k6 load on `/feed`, `/search`, `/placements`. **Security:** ZAP baseline; dependency audits.
* **UAT Scripts:** role‑based checklists for user, provider, admin.

---

## 22) Migration & Backfill Plan

1. Put site in maintenance window. 2. Run migrations idempotently. 3. Backfill `h3_index` and `search_snapshots`. 4. Reindex search engine. 5. Warm caches; pre‑render landing. 6. Toggle read‑only to read/write. 7. Monitor.

Rollback: drop new FKs, revert schema, keep append‑only logs.

---

## 23) Analytics & KPIs

* Feed CTR, search to contact rate, bid win rate, escrow funded rate, dispute rate, affiliate ROI, ad eCPM, store AOV, LTV, churn.

---

## 24) Detailed Task Breakdown (WBS)

**A. Live Feed** (BE+FE+Apps)

* Migrations (jobs/bids/q&a/events/media) → Seeders → API → Socket channels → Web UI cards → Flutter pages → Tests.

**B. Taxes & Zones**

* Migrations (tax_profiles/zone_polygons) → Admin zone editor (Leaflet draw) → Tax engine service → Checkout integration → Tests.

**C. Unified Search**

* Meilisearch schemas → Indexers → Query endpoint → Web grid & filters → Flutter search grid → Ranking tests.

**D. Escrow & Disputes**

* Escrow service + Stripe integration → Dispute workflow + Admin console → Notifications & timers → E2E tests.

**E. Storefront**

* Catalog + Orders + Shipping → Provider store UI → Checkout → Fulfillment webhooks → Tests.

**F. Ads & Banners**

* Slot service + campaign mgmt → client renderers → tracking beacons.

**G. Affiliates**

* Tracking, attribution, dashboard, payouts export.

**H. Security, Moderation, Scanning**

* Rule engine, AV pipeline, admin queues.

**I. UI/UX Overhaul**

* Design tokens, cards, dashboards, accessibility.

**J. Performance**

* Indexes, caching, image pipeline, bundle audit.

**K. Mobile Parity**

* Implement all feature screens, background services, push; TestFlight/Play alpha groups (12 testers).

---

## 25) Example Controllers/Routes (Laravel)

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/feed', [FeedController::class, 'index']);
    Route::apiResource('jobs', JobController::class);
    Route::post('jobs/{job}/bids', [BidController::class, 'store']);
    Route::post('jobs/{job}/questions', [JobQuestionController::class, 'store']);

    Route::post('escrows', [EscrowController::class, 'store']);
    Route::post('escrows/{escrow}/release', [EscrowController::class, 'release']);
    Route::post('escrows/{escrow}/refund', [EscrowController::class, 'refund']);

    Route::post('jobs/{job}/disputes', [DisputeController::class, 'open']);
    Route::post('disputes/{dispute}/message', [DisputeController::class, 'message']);
    Route::post('disputes/{dispute}/advance', [DisputeController::class, 'advance'])->middleware('can:admin');

    Route::get('search', [SearchController::class, 'index']);

    Route::get('placements/{slot}', [PlacementController::class, 'show']);

    Route::apiResource('stores', StoreController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);

    Route::get('affiliates/me', [AffiliateController::class, 'me']);
    Route::post('affiliates/links', [AffiliateController::class, 'createLink']);
});
```

---

## 26) Flutter Retrofit Example (Feed)

```dart
@RestApi(baseUrl: '/api/v1')
abstract class FeedApi {
  factory FeedApi(Dio dio, {String baseUrl}) = _FeedApi;
  @GET('/feed')
  Future<Paginated<FeedItem>> list(@Queries() Map<String, dynamic> filters);
}
```

---

## 27) Notifications & Chat

* In‑app chat bubbles (user<->provider; add support queue). Typing indicators, read receipts, attachment scanning. Push events on bids/questions/disputes/escrow changes.

---

## 28) Banners & Slow UI Fixes

* Replace synchronous banner loading with async placements; lazy load images; measure with Web Vitals; remove blocking CSS/JS.

---

## 29) Compliance: Provider‑Set Taxes (Global)

* Providers required to declare tax scheme & rate where applicable; validation per country; display on invoices; store tax evidence.

---

## 30) Seeders & Demo Data

* Factories for jobs/bids/providers/stores/products/ads/affiliates to populate Live Feed and Search for testing and screenshots.

---

## 31) Runbooks

* **Deploy:** maintenance → migrate → reindex → cache:clear → config:cache → horizon:terminate → queue:restart → warmup.
* **Incident:** roll back feature by env flag; toggle read‑only; replay failed jobs; rotate keys.

---

## 32) Acceptance Checklists (Condensed)

* **Live Feed:** real‑time, filters, geo, images, bids, Q&A, WS updates.
* **Taxes/Zones:** correct rates, zone matches, invoices show tax lines.
* **Search:** single box, fast (<300ms), accurate rank, grid UI.
* **Escrow/Disputes:** full flows incl. partial refunds; audit trail.
* **Storefront:** list → buy → ship → track; returns/disputes.
* **Ads:** slots fill, tracking works, capping enforced.
* **Affiliates:** link → signup → conversion → payout ledger.
* **Mobile:** parity with web; offline basics; push OK.
* **Performance:** p95 API < 400ms; LCP < 2.5s; CLS < 0.1.
* **Security:** SAST/DAST clean; CSP; MFA; AV scans.

---

### Appendix A — Entity Diagram (Text)

Users ⟷ Profiles ⟷ Tags (morph)
Users ⟷ Jobs ⟷ Bids ⟷ Escrows ⟷ Transactions
Jobs ⟷ JobQuestions ⟷ JobEvents
Users ⟷ Disputes ⟷ DisputeMessages
Providers ⟷ Stores ⟷ Products ⟷ Orders ⟷ Fulfillments
Users ⟷ Affiliates ⟷ AffiliateAttributions ⟷ AffiliatePayouts
AdCampaigns ⟷ AdCreatives ⟷ (Impressions/Clicks)
TaxProfiles ⟷ (Jobs | Orders)
ZonePolygons ⟷ (Jobs geo | Shipping rules)

### Appendix B — Env & Secrets Checklist

* STRIPE_* / ESCROW_* / SHIP_* / PUSHER_* / SEARCH_* / MAPS_* / SENTRY_* / FIREBASE_* / CSP_* / COOKIE_KEYS

### Appendix C — i18n & Currency

* Translations via Laravel Lang + Flutter arb; multi‑currency display with FX rates cache.

---

> **Deliverable:** With this guide, GPT‑Codex (or any dev) can implement, test, and launch the full Fixit + Taskup upgrade across web/admin/mobile with robust performance, security, and usability.
