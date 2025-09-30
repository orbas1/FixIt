# FixIt Platform Wireframes — Post-Upgrade Vision

These upgraded wireframes reflect the target experience after implementing marketplace, commerce, analytics, and security enhancements tailored to FixIt’s services marketplace. Every flow maps settings, branching logic, and integration points to support multi-crew service companies, scheduled service packages, equipment logistics, and warranty-backed engagements.

---

## 1. Web Application

### 1.1 Admin Dashboard Panel (Next-Gen)

```
┌────────────────────────────────────────────────────────────────────────────┐
│ Global Command Bar                                                         │
│ ├─ Search entities (customers/facilities/work orders/crews/stores)         │
│ ├─ Quick create (Service Package, Work Order, Campaign, Zone, Announcement)│
│ ├─ Automation status indicator (Queues healthy, Dispatch alerts)           │
│ └─ Profile menu (Profile, My Tasks, Notifications, Switch Org, Sign Out)   │
├────────────────────────────────────────────────────────────────────────────┤
│ Left Navigation (role-aware)                                               │
│ 1. Mission Control (overview)                                              │
│ 2. Marketplace Ops (service requests, disputes, warranties, moderation)    │
│ 3. Service Delivery (crew dispatch, route optimization, equipment logs)    │
│ 4. Commerce (storefronts, service bundles, inventory, orders, coupons)     │
│ 5. Growth (ads, affiliates, retention campaigns, experiments)              │
│ 6. Insights (dashboards, analytics studio, anomaly detection)              │
│ 7. Automation (workflows, webhooks, integrations)                          │
│ 8. Security Center (rules, incidents, audits)                              │
│ 9. Settings (org + platform)                                               │
│10. Support Desk (queues, SLAs, satisfaction)                               │
├────────────────────────────────────────────────────────────────────────────┤
│ Mission Control Canvas                                                     │
│ ├─ KPI Grid (MRR, GMV, Escrow balance, Warranty claim rate, Risk score)    │
│ ├─ Live Ops Timeline (dispatch incidents, disputes, escalations)           │
│ ├─ Automation Monitor (failed jobs, workflow latency)                      │
│ ├─ Experiment Snapshot (service bundle tests, retention offers)            │
│ └─ Alerts Center (filterable by severity & domain)                         │
└────────────────────────────────────────────────────────────────────────────┘
```

**Logic & Option Mapping**

| Interaction | Resulting Flow | Data/Automation |
|-------------|----------------|-----------------|
| Command bar search (e.g., `facility:ACME`) | Opens overlay with entity summary cards + quick actions (Edit, View Timeline, Dispatch Crew, Run Audit). | Hits consolidated search (Meilisearch) with service taxonomy facets; logs search analytics. |
| Selecting **Marketplace Ops → Disputes & Warranties** | Displays triage board segmented by SLA and warranty status. Buttons: Escalate, Offer Settlement, Schedule Inspection. | Triggers dispute workflows; inspection scheduling interfaces with inspector availability calendar. |
| **Service Delivery → Crew Dispatch** | Map view with crew telematics, job queue, equipment readiness status. Actions: Reassign Crew, Trigger Standby Crew, Flag Equipment Maintenance. | Integrates with routing engine; updates `crew_status` and sends push to impacted devices. |
| **Commerce → Service Bundles** | Tabbed view (Bundles, Add-ons, Pricing rules). Inline editing with autosave and audit trail. | Uses autosave with optimistic updates; writes to `service_bundle_versions` for history and pushes to storefront. |
| **Growth → Campaigns** | Canvas with campaign cards, funnel metrics, budget pacing gauge. CTA **Launch Retention Drip** duplicates campaign with service-plan targeting. | On launch, schedules background simulation and updates telemetry dashboards. |
| **Automation → Workflows** | Visual builder (nodes: trigger, condition, action). Save validates and deploys to workflow engine. | Deploy creates versioned workflow; publishes to queue registry; can target service packages or dispatch events. |
| **Security Center → Zero Trust Mode** | Policy wizard (device posture, IP ranges, geo restrictions, crew device attestation). | Updates policy config + notifies via multi-channel (email, push, Slack webhook). |
| Global command bar `/new inspection` | Prefills inspection scheduling wizard with blank template. | Creates draft inspection record; autosaves every 30s. |

**Advanced Settings Coverage**

* Org Profile: multi-brand theming, service taxonomy, SLA targets by service category.
* Financial: multi-currency accounts, tax remittance schedules, warranty reserve ledger, ledger export destinations (S3, BigQuery).
* Integrations Marketplace: toggles for partner services (Shippo, EasyPost, Twilio, Segment, fleet telematics) with credential vault references.
* Security: SSO providers, hardware key enforcement, session anomaly thresholds, audit retention duration.
* Automation: Workflow library, scheduled jobs calendar, API token management with scopes.
* Service Delivery: Crew certification matrix, equipment lifecycle rules, partner referral agreements, emergency dispatch protocols.
* Insights: Dashboard builder permissions, data freshness SLA thresholds, annotation controls.

### 1.2 User Types — Upgraded Web Experience

#### 1.2.1 Customer (Service Requester)

```
Dashboard Layout: Hero (Service Plan Suggestions) | Active Engagements | Recommended Service Companies | Financial & Warranty Snapshot | Experience Score
```

| Section | Enhancements | Logic |
|---------|--------------|-------|
| Hero | Guided task composer with templates (Cleaning Plan, HVAC Tune-up, Emergency Repair). Selecting template pre-fills visit cadence, crew size, materials. | Template selection fetches recommended services, auto-estimates budget via pricing engine, and surfaces warranty add-ons. |
| Active Engagements | Timeline cards showing milestones, crew en-route tracking, escrow status, attached deliverables, warranty expiration. Buttons: Approve Milestone, Raise Issue, Extend Plan, Request Different Crew. | Approve runs release job; Raise Issue opens guided dispute intake with severity rating; Extend Plan generates new schedule. Crew change checks provider roster compatibility. |
| Recommended Service Companies | Personalization slider (Value vs. Premium). Option to add to Favorites or invite to private service brief. | Slider updates API query with weighting; invitations send multi-provider RFP with service scope wizard. |
| Financial & Warranty Snapshot | Chart of spending, wallet balance, warranty reserves, loyalty points. CTA **Redeem Rewards**. | Rewards redemption opens marketplace modal to apply coupons/storefront credits or warranty discounts. |
| Experience Score | Feedback prompts, NPS, referral program, service quality tips. | Completing NPS triggers referral offer, updates analytics, and adjusts personalization weightings. |

Settings now include: communication cadence (digest frequency), privacy controls per data category, saved payment profiles, connected smart-home integrations with OAuth revocation, warranty auto-renew preferences.

**Decision Trees**

* **Raise Issue Flow**: Choose category (Quality, Safety, Missed Visit, Warranty Claim) → severity rating → auto-suggested resolution (refund %, extend timeline, mediation, inspection). Selecting inspection schedules crew/inspector visit via integrated calendar.
* **Redeem Rewards**: Choose reward type (Coupon, Store Credit, Warranty Extension, Partner Offer). Coupon requires selecting applicable work order; warranty extension shows prorated pricing.
* **Template Customization**: Editing template opens configuration modal to adjust steps (materials, crew size, warranty coverage). Saving creates user-specific template stored in profile.

#### 1.2.2 Service Provider (Service Company)

| Module | Upgraded UI | Logic |
|--------|-------------|-------|
| Lead Engine | Heatmap of demand by service zone, AI-suggested quotes, auto-response templates, competitor benchmarking. | Accepting AI suggestion opens edit modal; confirm sends enriched proposal and logs into analytics. Benchmarks highlight pricing gaps. |
| Work Order Command Center | Collaborative timeline with checklist, file vault, warranty obligations, compliance reminders, equipment checkout log. | Each checklist item updates progress %; compliance reminder integrates with policy rules. Warranty tab enforces documentation before completion. |
| Dispatch & Routing | Map with optimized routes, travel buffers, crew telemetry, stand-by crew suggestions, storm/outage overlays. | Accepting route updates pushes to Google/Apple; conflicts highlight and offer reschedule or partner referral. |
| Commerce & Inventory | Unified portal for service bundles, add-on products, consumable inventory, supplier ordering. | Inventory adjustments sync to storefront and provider mobile app; low-stock triggers recommended supplier order. |
| Earnings & Forecast | Cashflow projection, tax withholding settings, warranty reserve health, payouts across currencies. | Forecast uses machine learning model; user can simulate scenarios; exports to accounting integrations. |
| Team Hub | Role management, permission matrix, workload distribution, certification tracking. | Inviting member selects role (Admin/Dispatcher/Field Tech/Finance); scopes apply to dashboards and APIs; certification expiry triggers alerts. |

Settings enhancements: dynamic pricing rules, service coverage polygons, equipment inventory with maintenance reminders, compliance documents with expiry alerts, insurance policy uploads, partner referral agreements, warranty tiers.

**Automation Hooks**

* **AI Quote Assist**: When provider edits AI suggestion, difference logged to analytics for training feedback.
* **Compliance Reminders**: Failing to upload expiring documents locks certain job actions until resolved; unlocking requires supervisor approval.
* **Team Hub Permissions**: Drag-and-drop matrix defining module access (View/Edit/Approve). Publishing changes sends notifications to impacted members and logs to audit trail.
* **Inventory Sync**: Commerce manager updates propagate to storefront after queue processing; estimated sync time shown; failure triggers alert workflow.
* **Warranty Tracker**: Completion event triggers warranty follow-up tasks; overdue follow-up escalates to support queue.

#### 1.2.3 Support, Moderation & Field Safety User

* Unified queue combining tickets, flagged content, fraud alerts, warranty claims, on-site safety incidents with skill-based routing.
* Knowledge base integration; selecting article inserts macro + references tailored to service category.
* Playbook runner for escalations, linking to incident dashboard and dispatch timeline.
* Settings: shift scheduling, proficiency tags, notification thresholds, auto-assignment rules, field safety protocols.
* **Moderation Actions**: Approve, Reject, Escalate. Reject requires policy code selection and optional coaching note.
* **Field Safety Console**: Visual map of incidents, PPE compliance checklist, ability to pause crews or escalate to operations manager.
* **Escalation Outcome Tracker**: Displays status of previously escalated incidents with resolution SLA progress, warranty implications, and customer satisfaction scores.

---

## 2. Phone Application (Flutter)

### 2.1 Admin Mobile (Next-Gen)

```
Auth → Biometrics → Dashboard Tabs: Command | Ops | Service Delivery | Commerce | Security | Settings
```

* **Command Tab**: Interactive cards with voice command input, real-time KPIs (Dispatch readiness, Warranty backlog), anomaly badges. Tap anomaly → drill into detail screen.
* **Ops Tab**: Swimlane view for service requests/disputes/warranty claims; filtering by SLA, service category, or geo. Bulk actions (assign, escalate, schedule inspection) via multi-select.
* **Service Delivery Tab**: Crew tracker with live GPS, equipment status, push-to-talk integration for field supervisors.
* **Commerce Tab**: Service bundles, mobile storefront editor preview, push notifications for low stock or expiring promos.
* **Security Tab**: Incident widgets (Live, Recent, Resolved), toggle Zero Trust, device trust list management, crew device attestation status.
* **Settings Tab**: Account (delegate access), Notifications (per severity push/email/SMS), Operations (default emergency protocols), Theme (light/dark/high contrast), Security (session limits, hardware key pairing), Integrations (Slack, PagerDuty webhooks).

Logic: Offline mode caches last 24h data; upon reconnect, sync queue processes actions with conflict resolution prompts. Conflicts show comparison (server vs. device) before confirming.

**Interaction & Edge Cases**

* **Voice Commands**: Supports “Show crews delayed over 30 min” or “Launch warranty audit”; fallback displays command guide if speech fails.
* **Bulk Actions**: Selecting multiple incidents prompts confirmation slider; includes audit note requirement.
* **Device Trust List**: Swipe left to quarantine a device; prompts to notify user and revoke sessions; requires secondary approval for crew device lockdown.
* **Conflict Resolution**: If offline update conflicts with latest server state, user must choose server copy vs. keep local changes; audit trail records decision.

### 2.2 Customer Mobile App (Upgraded)

* **Personalized Feed**: Cards (Nearby Service Companies, Active Work Orders, Offers, Warranty Reminders). Option to pin categories. Scroll triggers lazy load analytics events.
* **360 Work Order Tracker**: Milestone timeline, crew en-route map, document vault, live chat, video call button. Raise issue opens mini-dispute wizard with photo capture and warranty tagging.
* **Marketplace Tab**: Shop service add-ons, consumables, and partner products. Cart, wishlist, promo codes, AR preview for products (e.g., replacement fixtures). Checkout uses saved payment + escrow credits + warranty coverage where applicable.
* **Wallet & Rewards**: Multi-currency balances, cashback, referral tracking, warranty reserve contributions. Toggle auto-top-up, set savings goals, configure warranty auto-renew.
* **Profile & Settings**: Accessibility options (text scaling, voice-over), privacy toggles per contact method, connected services (calendar, smart locks, building access systems), security (biometrics, passcode, login history, trusted household members).

**Flows & Logic Enhancements**

| Feature | States | Logic |
|---------|--------|-------|
| Personalized Feed | Default, Category pinned, Alert mode (if urgent). | Alert mode triggered when SLA breach detected or warranty claim pending; displays red accent card linking to support. |
| 360 Work Order Tracker | Timeline, Files, Approvals, Payments, Warranty. | Payments tab allows splitting payments across funding sources (card + wallet + warranty credit). |
| Marketplace | Browse, AR Preview, Cart, Checkout. | AR preview requires camera permission; fallback to static preview. Warranty-based pricing adjusts automatically. |
| Wallet & Rewards | Balances, Goals, Referrals, Warranty Reserve. | Setting savings goal triggers monthly reminders; referral tracking shows conversion stages; warranty reserve contributions locked until claim. |
| Security | Login history list, device management, household sharing. | Removing device logs out sessions and requests justification note. Household sharing prompts secondary verification. |

**Settings Deep-Dive**: Allows scheduling quiet hours, customizing push categories, linking IoT devices (smart locks, thermostats) with revocation, managing household members’ permissions.

### 2.3 Provider Mobile App (Upgraded)

* **Mission Dashboard**: Pipeline summary, urgent alerts, AI insights (“Three HVAC tune-ups expiring warranty in 7 days”).
* **Lead Inbox**: Multi-tab (Suggested, Matched, Expiring). Quick reply templates, price suggestion slider, video estimate option, service plan upsell prompts.
* **Work Order Command Center**: Work orders grouped by crew; timeline, asset checklists, expense logging (scan receipts), milestone sign-off with customer signature capture, warranty obligations widget.
* **Schedule & Dispatch**: Drag-and-drop timeline, crew assignment, travel optimization, in-app navigation integration, weather overlays.
* **Commerce Manager**: Manage service bundles, add-on products, inventory counts, barcode scanner integration, dynamic pricing alerts, supplier reorder suggestions.
* **Inventory & Equipment**: Dedicated tab to manage equipment assignments, maintenance logs, warranty tracking for owned assets.
* **Settings**: Team permissions, insurance/compliance uploads with reminders, payout routing rules (split percentages), AI assistant preferences (tone, automation level), partner referral management.

**Detailed Logic**

* **Mission Dashboard Alerts**: Tapping alert opens action sheet (Snooze, Act Now, Assign, Escalate). Snooze prompts duration; action recorded in audit log with crew + work order context.
* **Lead Inbox Expiring Tab**: Countdown timer; when <1 hour, push notification sent; leads auto-expire into referral queue if not accepted.
* **Work Order Command Center**: Expense logging uses OCR; failed OCR allows manual entry. Warranty widget enforces capture of completion proof before closing.
* **Schedule & Dispatch**: Dragging job to new slot prompts to notify customer and crew; requires reason code; updates route optimization.
* **Commerce Manager**: Pricing alert prompts to adjust service bundle or justify variance; approvals logged.
* **Payout Routing**: Rules defined per service type; split percentages must total 100% or validation error shown. Supports warranty reserve contributions.

### 2.4 Additional Roles (Inspector/Moderator Mobile)

* Inspection applet for verifying completed jobs: checklist, photo capture, risk scoring, warranty compliance confirmation.
* Moderator mobile queue: flagged content review, audio transcription playback, crew safety incident review.
* Inspector flow includes offline checklist caching; sync prompts review summary before upload; requires signature capture.
* Moderator queue integrates voice-to-text toggle; appeals button routes to web console with work order context.

---

## 3. Firebase (Upgraded Wireframe)

```
Firebase Project: fixit-platform-next
├─ Auth
│  ├─ Providers: Email/Password, Google, Apple, Microsoft, Magic Link
│  ├─ MFA: TOTP + SMS backup, Device Check attestation, crew device posture
│  ├─ Custom Claims: role, orgId, permissions[], serviceAreas[], featureFlags
│  └─ Blocking functions for risk-based login + warranty claim throttling
├─ Firestore / Firestore+Datastore split
│  ├─ collections
│  │   ├─ notifications (localized payloads)
│  │   ├─ device_tokens (with device posture + crew status)
│  │   ├─ audit_logs (streamed to BigQuery via Dataflow)
│  │   ├─ experiments (service bundle tests)
│  │   ├─ offline_queue (mobile action sync)
│  │   ├─ warranty_claims
│  │   └─ partner_referrals
│  └─ Rules with attribute-based access control + environment segmentation
├─ Cloud Messaging
│  ├─ Topics: global, work_order_{id}, crew_{id}, campaign_{id}, security_incident, warranty_alerts
│  └─ Condition-based pushes using user segments (household vs. facility)
├─ Storage
│  ├─ Buckets: uploads/temporary, uploads/secure, compliance/archive, warranty/proofs
│  └─ Rules enforcing AV scan + metadata tags (service category, warranty ID)
├─ Extensions
│  ├─ Firestore-bigquery-export
│  ├─ Auth user import/export automation
│  └─ Run custom logic on document create/update (dispatch + warranty webhooks)
└─ Cloud Functions (expansive)
    ├─ workflowDispatcher (handles automation events)
    ├─ incidentNotifier (routes security + field safety alerts)
    ├─ storefrontInventorySync (update Meilisearch)
    ├─ experimentAllocator (assigns variants across personas)
    ├─ complianceArchivist (moves docs to archive bucket)
    ├─ warrantyAdjudicator (auto-triages warranty claims)
    └─ referralRouter (manages partner referrals & revenue share)
```

**Logic Enhancements**

* Device tokens include posture (biometric, jailbreak status) and crew assignment; high-risk devices blocked from sensitive actions.
* Offline queue ensures mobile writes sync via Cloud Functions conflict resolver; conflicts recorded to audit.
* Experiment allocator ensures consistent variant assignment across web/mobile using Firestore transactions keyed by service persona.
* Warranty adjudicator evaluates claim metadata, cross-checks completion evidence, and updates status + notifications.
* Compliance archivist moves docs post-retention; audit event written to BigQuery with signature.
* Risk scoring functions evaluate login metadata and update custom claims to enforce step-up auth, especially for dispatch-sensitive actions.

---

## 4. Security (Upgraded Wireframe)

```
Security Operations Center (SOC) UI
├─ Overview Wall
│  ├─ Threat Level indicator (color-coded)
│  ├─ Active Incidents map (geo overlay with service regions)
│  ├─ KPI tiles (Mean time to detect/respond, Zero Trust coverage, Crew device compliance)
├─ Detection Hub
│  ├─ Rules Library (text/media/behavioral/service anomalies)
│  ├─ Machine Learning alerts (anomaly scores)
│  ├─ Integrations (SIEM, SOAR, IDS, telematics) status
├─ Response Console
│  ├─ Incident timeline with tasks, assignees, SLA countdown, field safety notes
│  ├─ Playbook automation builder (drag/drop)
│  ├─ Evidence locker (files, notes, approvals, warranty attachments)
├─ Compliance Center
│  ├─ Policy attestations, audit scheduler, regulator export
│  ├─ Data privacy dashboard (DSAR queue, retention timers)
│  └─ Encryption key rotation tracker
└─ Settings & Governance
   ├─ Access control matrix (roles, scopes, hardware keys)
   ├─ Network controls (IP/ASN, geo fencing, API rate plans)
   ├─ Secrets management (vault references, rotation reminders)
   └─ Reporting automations (weekly digest, incident drill schedule)
```

**Logic Mapping**

* Selecting anomaly alert opens detailed analysis (event timeline, impacted assets, remediation suggestions). Operators can approve automated containment (disable account, revoke crew device, pause work order).
* Playbook automation builder allows conditional branches (if severity ≥ High → notify PagerDuty + Slack; else → create Jira ticket). Publishing version increments and requires dual approval.
* Compliance exports support scheduling; selecting regulator (GDPR, CCPA, state-specific home services regs) tailors dataset and anonymization. Completion logs to audit ledger.
* Access control matrix edits propagate to IAM service; high-risk changes require MFA re-auth and supervisor approval.
* SOC Overview Wall supports “war room” mode; enables shared annotations and pinned alerts for shift hand-off.
* Threat intel feed ingestion can be paused; prompts to choose duration and justification; service impact summary required.

---

## 5. Settings Matrix (Post-Upgrade)

| Domain | Settings | Logic/Dependencies |
|--------|----------|--------------------|
| Communications | Multi-channel orchestration (email/SMS/push/in-app/voice), quiet hours, escalation rules, service disruption templates. | Depends on orchestration engine; validations ensure fallback channel defined per severity. |
| Payments & Escrow | Multi-currency wallets, split payouts, automated tax withholding, escrow milestones, risk holds, warranty reserve contributions. | Integrates with ledger + risk engine; releasing funds checks compliance + warranty state. |
| Commerce | Storefront theming, inventory buffers, supplier catalogs, service bundles, promotions (stacking rules), fulfillment SLAs. | Syncs to search index + analytics; validations prevent overlapping promo conflicts; equipment availability gating. |
| Automation | Workflow templates, service webhooks, sandbox testing mode, version rollback, inspection scheduling rules. | Requires passing validation suite before activation; sandbox runs with mock data. |
| Privacy & Compliance | Data residency zones, consent records, DSAR automation, retention policies by entity, warranty document retention. | Policy enforcement engine updates jobs to purge/archive data; surfaces countdown to deletion. |
| Accessibility & Localization | Theme packs (light/dark/high contrast), text scaling defaults, locale fallback chains, service terminology translation review queue. | Rendering layer consumes tokens; translation queue integrates with linguist workflow. |
| Security | Zero Trust policies, device posture requirements, behavioral analytics thresholds, API key rotation cadence, crew device attestation. | Device policy stored in Firebase claims; API keys tracked with expiration; anomaly thresholds feed ML pipeline. |
| Insights & Analytics | Dashboard authorship permissions, metric definitions, anomaly detection thresholds, data export schedules, warranty analytics. | Exports require token-based auth; anomaly thresholds update detection ML jobs. |
| Support Operations | Auto-assignment rules, queue prioritization, satisfaction survey cadence, macros governance, field safety escalation paths. | Macro updates require review workflow; survey cadence tied to CRM triggers; safety escalations integrate with dispatch. |

---

These wireframes encapsulate the desired end-state, providing a blueprint for engineering, design, and product teams to deliver the comprehensive FixIt services-platform upgrade roadmap.
