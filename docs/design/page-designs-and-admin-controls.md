# FixIt Page Designs, Screens, and Admin Controls

> **Goal:** Deliver exhaustive page-level specifications covering current state and upgraded blueprint for all FixIt web and phone experiences, including admin controls for service inserts and editing.

## 1. Current Page Inventory

### 1.1 Web Application

| Persona | Page | Key Sections | Controls |
| --- | --- | --- | --- |
| Customer | Dashboard | Active Services, Upcoming Visits, Warranty Summary | `Schedule Service`, `Chat Support`, `Extend Warranty` |
| Customer | Service Request Form | Address, Service Type, Preferred Crew, Warranty Add-on | Multi-step wizard, attachments |
| Provider | Provider Console | Live Requests, Crew Availability, Inventory | `Assign Crew`, `Pause Request`, `Edit Package` |
| Serviceman | Crew Board | Job Queue, Route Map, Checklist | `Accept Job`, `Start Route`, `Complete Task` |
| Admin | Dispatch Control | Map grid, Crew tiles, Incident alerts | `Reassign Crew`, `Escalate`, `Suspend Provider` |
| Admin | Content Inserts | Landing hero CMS, Service categories, Promo banners | `Add Section`, `Upload Media`, `Publish` |

```
+------------------------------------------------+
| Dispatch Control                                |
|  ┌────────────┐  ┌────────────┐  ┌───────────┐ |
|  | Crew 01    |  | Crew 02    |  | Crew 03   | |
|  | En route   |  | On site    |  | Standby   | |
|  └────────────┘  └────────────┘  └───────────┘ |
|  Map + Warranty Alerts Banner                   |
+------------------------------------------------+
```

### 1.2 Mobile Application

| Persona | Screen | Primary Widgets | Controls |
| --- | --- | --- | --- |
| Customer | Home Feed | Service cards, Warranty reminders | `Book`, `Upgrade`, `Chat` |
| Customer | Request Wizard | Stepper, address picker, crew selector | `Next`, `Save Draft` |
| Provider | Dispatch | Timeline, crew availability chips | `Assign`, `Delay`, `Add Note` |
| Provider | Packages | Package tiles, pricing inputs | `Add Package`, `Duplicate`, `Archive` |
| Serviceman | Job Detail | Checklist, map, timers | `Check In`, `Upload Proof`, `Complete` |
| Admin | Mobile Admin | Alerts feed, crew statuses | `Acknowledge`, `Escalate`, `Call Crew` |

## 2. Settings & Inserts (Current)

* **CMS Landing Inserts:** Basic WYSIWYG for hero text, service categories, partner logos.
* **Warranty Defaults:** Hard-coded durations; admin can toggle but lacks preview.
* **Provider Packages:** Price and duration fields; no tier templating.
* **Dummy Text:** Hardwired lorem ipsum for new sections; requires manual replacement.

## 3. Upgrade Blueprint

### 3.1 Web Pages

#### 3.1.1 Admin Dashboard (After Upgrades)

* **Top Metrics Ribbon:** Jobs dispatched today, Active crews, SLA compliance, Warranty renewals.
* **Command Center Canvas:** Drag-and-drop crew assignment over territory map.
* **Incident Lane:** Cards for escalations, compliance checks.
* **Service Package Library:** Inline editing grid with version history.
* **Branding Console:** Preview of landing hero, promo cards, trust badges.
* **Controls:**
  * `Add Crew Shift`, `Lock Territory`, `Trigger Warranty Outreach`.
  * `Import Logo`, `Reset to Defaults`, `Apply Theme`.
  * `Insert Dummy Text`, `Purge Placeholder`, `Generate Copy`.

```mermaid
flowchart LR
  Metrics --> CommandCenter --> IncidentLane --> PackageLibrary --> BrandingConsole
```

#### 3.1.2 Customer Web Journey

* **Landing:** Hero with service CTA, carousel of providers, warranty badge.
* **Unified Search:** Filter chips (service type, response time, warranty coverage).
* **Service Planner:** Stepper (Scope → Crew → Schedule → Warranty → Review).
* **Warranty Locker:** Table of warranties, download buttons, extend/transfer actions.
* **Settings:** `Profile`, `Addresses`, `Payment`, `Warranty Preferences`.

#### 3.1.3 Provider Web Journey

* **Provider HQ:** Summary cards (Open jobs, Crew health, Earnings, Reviews).
* **Crew Planner:** Kanban columns (Requested, Scheduled, En Route, On Site, Completed).
* **Package Builder:** Tier editor with `Starter`, `Pro`, `Premium` templates, upsell suggestions.
* **Inventory & Tools:** Manage equipment with maintenance reminders.
* **Brand Studio:** Upload logos, set accent colors, manage landing insert order.

#### 3.1.4 Serviceman Web Journey (for kiosk)

* **Daily Briefing:** Timeline of assigned jobs, territory heatmap.
* **Job Console:** Step-by-step checklists, photo upload, warranty compliance.
* **Support Channel:** Quick contact to dispatch or provider lead.

### 3.2 Mobile Screens (After Upgrades)

* **Admin App:**
  * Dashboard → `Crew Status Ring`, `SLA Heatmap`, `Warranty Alerts`.
  * Screens: `Dispatch Map`, `Incident Queue`, `Package Approvals`, `Branding Updates`.
  * Controls: `Reassign`, `Hold Payment`, `Approve Branding`, `Push Update`.
* **Customer App:**
  * Tabs: Home (recommendations), Schedule (timeline), Warranty (digital cards), Support (chat + FAQs).
  * Screens include `Quick Request`, `Service Tracker`, `Warranty Claim`, `Invoice Viewer`.
* **Provider App:**
  * Tabs: Dispatch (board view), Packages (editor), Crew (roster), Finance (wallet & payouts).
  * Screens: `Crew Shift Editor`, `Service Package Template`, `Performance Analytics`.
* **Serviceman App:**
  * Tabs: Queue, Route, Tasks, Profile.
  * Screens: `Job Countdown`, `On-site Checklist`, `Warranty Capture`, `Post-Service Survey`.

```
┌────────────────────────────┐
│ Provider Mobile Dispatch    │
│ ┌────────┬────────┬───────┐ │
│ |Queued  |Scheduled|On Site| │
│ └────────┴────────┴───────┘ │
│ Drag crew between columns    │
└────────────────────────────┘
```

### 3.3 Inserts & Editing Controls

| Area | Control | Description |
| --- | --- | --- |
| Landing Builder | `Add Section`, `Reorder`, `Apply Layout` | Choose hero, testimonials, service categories; preview responsive states. |
| Logo Manager | `Upload`, `Crop`, `Set Default`, `Assign Provider` | Supports multiple brand kits per provider. |
| Dummy Text Studio | `Generate Copy`, `Mark Placeholder`, `Clear All` | Provides service-specific prompts; track where dummy text remains. |
| Warranty Defaults | `Set Duration`, `Add Coverage`, `Attach Terms` | Inline PDF preview; auto-sync to mobile wallet. |
| Service Packages | `Create Tier`, `Link Crew`, `Attach Add-on` | Connects to crew scheduling and pricing calculators. |

### 3.4 Forms & Validation

* **Admin Controls:** Multi-step modals with autosave, collaborative locking.
* **Provider Forms:** Real-time validation for coverage areas, price bounds, crew capacity.
* **Customer Forms:** Address auto-complete, warranty explanation tooltips.
* **Serviceman Forms:** Simple toggles with large tap targets, offline safe.

## 4. Data & Integration Hooks

* Use GraphQL or REST overlays for `pages`, `sections`, `brand_assets` tables.
* Audit trail for every change: `admin_changes` logging old/new values.
* Sync with Firebase for mobile configuration (page ordering, feature flags).
* Leverage CDN for uploaded assets, generate responsive derivatives.

## 5. Rollout Checklist

1. Inventory existing pages vs new blueprint.
2. Implement admin console modules with role-based access.
3. Build mobile and web layout components per above diagrams.
4. QA editing workflow: create insert → publish → rollback.
5. Train providers/servicemen via in-app tours and documentation.

---

> ASCII tiles above represent upgraded dispatch boards emphasising crew coordination.
